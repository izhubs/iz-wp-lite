<?php
/**
 * REST API Endpoints for Tours Catalog and Client Bookings.
 *
 * PHP version 8.1+
 *
 * @package IZTourEngine
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class IZ_Tour_REST_API
 *
 * Exposes headless REST API routes under the 'iz-tour/v1' namespace for:
 * 1. Filtered Tour Catalog retrieval (GET /wp-json/iz-tour/v1/tours).
 * 2. Instant Booking & VietQR generation (POST /wp-json/iz-tour/v1/book).
 *
 * DECISION: Native WP_REST_Controller pattern vs custom admin-ajax.php.
 * WHY: REST API provides standardized HTTP status codes (201, 400, 404),
 * native schema validation, and is directly consumable by modern headless frontends (Astro, Next.js).
 * REF: https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
 */
final class IZ_Tour_REST_API
{
    private const NAMESPACE = 'iz-tour/v1';

    /**
     * Register REST API routes on 'rest_api_init'.
     *
     * WHY: Connects custom routes to the WordPress core REST router.
     */
    public function register(): void
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Define route contracts and parameter schemas.
     *
     * WHY: Enforces strict HTTP methods, sanitize callbacks, and parameter types before execution.
     */
    public function register_routes(): void
    {
        // 1. Tours catalog route
        register_rest_route(self::NAMESPACE, '/tours', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [$this, 'get_tours'],
            'permission_callback' => '__return_true',
            'args'                => [
                'destination' => [
                    'description'       => __('Lọc theo slug hoặc ID điểm đến', 'iz-tour-engine'),
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'tour_type' => [
                    'description'       => __('Lọc theo slug hoặc ID loại tour', 'iz-tour-engine'),
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'min_price' => [
                    'description'       => __('Giá thấp nhất', 'iz-tour-engine'),
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ],
                'max_price' => [
                    'description'       => __('Giá cao nhất', 'iz-tour-engine'),
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ],
                'duration' => [
                    'description'       => __('Tìm theo thời lượng (VD: 3 Ngày)', 'iz-tour-engine'),
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'search' => [
                    'description'       => __('Từ khóa tìm kiếm', 'iz-tour-engine'),
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'page' => [
                    'description'       => __('Trang hiện tại', 'iz-tour-engine'),
                    'type'              => 'integer',
                    'default'           => 1,
                    'sanitize_callback' => 'absint',
                ],
                'per_page' => [
                    'description'       => __('Số lượng tour mỗi trang', 'iz-tour-engine'),
                    'type'              => 'integer',
                    'default'           => 12,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        // 2. Booking creation route
        register_rest_route(self::NAMESPACE, '/book', [
            'methods'             => \WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'create_booking'],
            'permission_callback' => '__return_true',
            'args'                => [
                'tour_id' => [
                    'required'          => true,
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ],
                'customer_name' => [
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'customer_phone' => [
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'customer_email' => [
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_email',
                ],
                'departure_date' => [
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'adult_count' => [
                    'type'              => 'integer',
                    'default'           => 1,
                    'sanitize_callback' => 'absint',
                ],
                'child_count' => [
                    'type'              => 'integer',
                    'default'           => 0,
                    'sanitize_callback' => 'absint',
                ],
                'infant_count' => [
                    'type'              => 'integer',
                    'default'           => 0,
                    'sanitize_callback' => 'absint',
                ],
                'customer_note' => [
                    'type'              => 'string',
                    'default'           => '',
                    'sanitize_callback' => 'sanitize_textarea_field',
                ],
                'payment_method' => [
                    'type'              => 'string',
                    'default'           => 'vietqr',
                    'sanitize_callback' => 'sanitize_key',
                ],
            ],
        ]);
    }

    /**
     * Retrieve a filtered collection of tours.
     *
     * WHY: Powers frontend tour directories, faceted filters, and search forms.
     *
     * @param \WP_REST_Request $request Incoming REST request.
     * @return \WP_REST_Response Response object.
     */
    public function get_tours(\WP_REST_Request $request): \WP_REST_Response
    {
        $page     = max(1, (int) $request->get_param('page'));
        $per_page = min(50, max(1, (int) $request->get_param('per_page')));

        $query_args = [
            'post_type'      => 'tour',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];

        // Search keyword
        $search = (string) $request->get_param('search');
        if (!empty($search)) {
            $query_args['s'] = $search;
        }

        // Taxonomies
        $tax_query = [];
        $destination = (string) $request->get_param('destination');
        if (!empty($destination)) {
            $tax_query[] = [
                'taxonomy' => 'tour_destination',
                'field'    => is_numeric($destination) ? 'term_id' : 'slug',
                'terms'    => is_numeric($destination) ? (int) $destination : $destination,
            ];
        }

        $tour_type = (string) $request->get_param('tour_type');
        if (!empty($tour_type)) {
            $tax_query[] = [
                'taxonomy' => 'tour_type',
                'field'    => is_numeric($tour_type) ? 'term_id' : 'slug',
                'terms'    => is_numeric($tour_type) ? (int) $tour_type : $tour_type,
            ];
        }

        if (!empty($tax_query)) {
            $query_args['tax_query'] = $tax_query;
        }

        // Meta Query (Price & Duration)
        $meta_query = [];
        $min_price = (int) $request->get_param('min_price');
        $max_price = (int) $request->get_param('max_price');
        $duration  = (string) $request->get_param('duration');

        if ($min_price > 0) {
            $meta_query[] = [
                'key'     => '_tour_price_adult',
                'value'   => $min_price,
                'type'    => 'NUMERIC',
                'compare' => '>=',
            ];
        }

        if ($max_price > 0) {
            $meta_query[] = [
                'key'     => '_tour_price_adult',
                'value'   => $max_price,
                'type'    => 'NUMERIC',
                'compare' => '<=',
            ];
        }

        if (!empty($duration)) {
            $meta_query[] = [
                'key'     => '_tour_duration',
                'value'   => $duration,
                'compare' => 'LIKE',
            ];
        }

        if (!empty($meta_query)) {
            $query_args['meta_query'] = $meta_query;
        }

        $query = new \WP_Query($query_args);
        $items = [];

        foreach ($query->posts as $post) {
            if (!$post instanceof \WP_Post) {
                continue;
            }

            $post_id = $post->ID;
            $thumb_id = get_post_thumbnail_id($post_id);
            $thumb_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'large') : false;

            // Destinations
            $dest_terms = get_the_terms($post_id, 'tour_destination');
            $destinations = is_array($dest_terms) ? array_map(static fn($t) => ['id' => $t->term_id, 'name' => $t->name, 'slug' => $t->slug], $dest_terms) : [];

            // Types
            $type_terms = get_the_terms($post_id, 'tour_type');
            $types = is_array($type_terms) ? array_map(static fn($t) => ['id' => $t->term_id, 'name' => $t->name, 'slug' => $t->slug], $type_terms) : [];

            // Dates & Itinerary
            $raw_dates = (string) get_post_meta($post_id, '_tour_departure_dates', true);
            $parsed_dates = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $raw_dates))));

            $raw_itinerary = (string) get_post_meta($post_id, '_tour_itinerary', true);
            $itinerary = !empty($raw_itinerary) ? json_decode($raw_itinerary, true) : [];

            $items[] = [
                'id'               => $post_id,
                'title'            => get_the_title($post_id),
                'slug'             => $post->post_name,
                'excerpt'          => get_the_excerpt($post_id),
                'permalink'        => get_permalink($post_id),
                'thumbnail_url'    => $thumb_url ?: '',
                'tour_code'        => (string) get_post_meta($post_id, '_tour_code', true),
                'duration'         => (string) get_post_meta($post_id, '_tour_duration', true),
                'departure_city'   => (string) get_post_meta($post_id, '_tour_departure_city', true),
                'transportation'   => (string) get_post_meta($post_id, '_tour_transportation', true),
                'price_adult'      => (int) get_post_meta($post_id, '_tour_price_adult', true),
                'price_child'      => (int) get_post_meta($post_id, '_tour_price_child', true),
                'price_infant'     => (int) get_post_meta($post_id, '_tour_price_infant', true),
                'price_single_sup' => (int) get_post_meta($post_id, '_tour_price_single_sup', true),
                'departure_dates'  => array_values($parsed_dates),
                'itinerary'        => is_array($itinerary) ? $itinerary : [],
                'destinations'     => $destinations,
                'tour_types'       => $types,
            ];
        }

        $response = [
            'items'        => $items,
            'total'        => (int) $query->found_posts,
            'total_pages'  => (int) $query->max_num_pages,
            'current_page' => $page,
            'per_page'     => $per_page,
        ];

        return new \WP_REST_Response($response, 200);
    }

    /**
     * Create a new tour booking and return instant VietQR payment info.
     *
     * WHY: Calculates authentic total based on database tour rates (not client claims),
     * registers private booking post, and prepares zero-friction VietQR payment link.
     *
     * @param \WP_REST_Request $request Incoming REST request.
     * @return \WP_REST_Response|\WP_Error
     */
    public function create_booking(\WP_REST_Request $request)
    {
        $tour_id        = (int) $request->get_param('tour_id');
        $customer_name  = trim((string) $request->get_param('customer_name'));
        $customer_phone = trim((string) $request->get_param('customer_phone'));
        $customer_email = trim((string) $request->get_param('customer_email'));
        $departure_date = trim((string) $request->get_param('departure_date'));
        $adult_count    = max(1, (int) $request->get_param('adult_count'));
        $child_count    = max(0, (int) $request->get_param('child_count'));
        $infant_count   = max(0, (int) $request->get_param('infant_count'));
        $customer_note  = (string) $request->get_param('customer_note');
        $payment_method = (string) $request->get_param('payment_method') ?: 'vietqr';

        // 1. Validation checks
        if ($tour_id <= 0 || get_post_type($tour_id) !== 'tour' || get_post_status($tour_id) !== 'publish') {
            return new \WP_Error('invalid_tour', __('Tour được chọn không tồn tại hoặc đã ngừng nhận khách.', 'iz-tour-engine'), ['status' => 404]);
        }

        if (empty($customer_name)) {
            return new \WP_Error('missing_name', __('Họ và tên khách hàng là bắt buộc.', 'iz-tour-engine'), ['status' => 400]);
        }

        if (empty($customer_phone) || strlen($customer_phone) < 9) {
            return new \WP_Error('invalid_phone', __('Số điện thoại không hợp lệ.', 'iz-tour-engine'), ['status' => 400]);
        }

        if (!is_email($customer_email)) {
            return new \WP_Error('invalid_email', __('Địa chỉ email không đúng định dạng.', 'iz-tour-engine'), ['status' => 400]);
        }

        if (empty($departure_date)) {
            return new \WP_Error('missing_date', __('Vui lòng chọn ngày khởi hành.', 'iz-tour-engine'), ['status' => 400]);
        }

        // 2. Pricing calculation (Enforce server-side authority)
        $price_adult  = (int) get_post_meta($tour_id, '_tour_price_adult', true);
        $price_child  = (int) get_post_meta($tour_id, '_tour_price_child', true);
        $price_infant = (int) get_post_meta($tour_id, '_tour_price_infant', true);

        $total_amount = ($adult_count * $price_adult) + ($child_count * $price_child) + ($infant_count * $price_infant);

        // 3. Unique Booking Code generation
        // Format: BK<Year><Month><Day><Random4>
        $booking_code = 'BK' . gmdate('ymd') . wp_rand(1000, 9999);

        // 4. Create tour_booking post
        $tour_title = get_the_title($tour_id);
        $booking_post_id = wp_insert_post([
            'post_title'   => sprintf('%s - %s - %s', $booking_code, $customer_name, $tour_title),
            'post_type'    => 'tour_booking',
            'post_status'  => 'publish',
            'ping_status'  => 'closed',
            'comment_status' => 'closed',
        ]);

        if (is_wp_error($booking_post_id) || $booking_post_id === 0) {
            return new \WP_Error('booking_failed', __('Không thể ghi nhận đơn đặt tour. Vui lòng liên hệ hotline.', 'iz-tour-engine'), ['status' => 500]);
        }

        // 5. Store metadata
        update_post_meta($booking_post_id, '_booking_code', $booking_code);
        update_post_meta($booking_post_id, '_booking_tour_id', $tour_id);
        update_post_meta($booking_post_id, '_booking_customer_name', $customer_name);
        update_post_meta($booking_post_id, '_booking_customer_phone', $customer_phone);
        update_post_meta($booking_post_id, '_booking_customer_email', $customer_email);
        update_post_meta($booking_post_id, '_booking_customer_note', $customer_note);
        update_post_meta($booking_post_id, '_booking_departure_date', $departure_date);
        update_post_meta($booking_post_id, '_booking_adult_count', $adult_count);
        update_post_meta($booking_post_id, '_booking_child_count', $child_count);
        update_post_meta($booking_post_id, '_booking_infant_count', $infant_count);
        update_post_meta($booking_post_id, '_booking_total_amount', $total_amount);
        update_post_meta($booking_post_id, '_booking_status', 'pending');
        update_post_meta($booking_post_id, '_booking_payment_method', $payment_method);

        // 6. Generate VietQR URL
        $vietqr_url = '';
        if ($total_amount > 0 && class_exists('IZ_Tour_VietQR')) {
            $vietqr_url = IZ_Tour_VietQR::build_qr_url($total_amount, $booking_code);
        }

        // Return structured payload
        $payload = [
            'success'        => true,
            'booking_id'     => $booking_post_id,
            'booking_code'   => $booking_code,
            'tour_id'        => $tour_id,
            'tour_title'     => $tour_title,
            'departure_date' => $departure_date,
            'guests'         => [
                'adults'  => $adult_count,
                'children' => $child_count,
                'infants' => $infant_count,
                'total'   => $adult_count + $child_count + $infant_count,
            ],
            'total_amount'   => $total_amount,
            'currency'       => 'VND',
            'status'         => 'pending',
            'payment_method' => $payment_method,
            'vietqr_url'     => $vietqr_url,
            'message'        => __('Đơn đặt tour đã được tạo thành công.', 'iz-tour-engine'),
        ];

        return new \WP_REST_Response($payload, 201);
    }
}
