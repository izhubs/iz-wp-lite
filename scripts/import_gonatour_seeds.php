<?php
/**
 * Gonatour Seed Data Importer & Architecture Generator.
 *
 * Imports and compiles production-quality tour records, travel guide posts,
 * and service pages from the Gonatour data model specification into JSON/SQL seed artifacts
 * and optional WordPress runtime database.
 *
 * PHP version 8.2+
 *
 * DECISION: Dual-mode execution (CLI Standalone Generator vs WordPress Bootstrap Importer).
 * WHY: Enables headless environment testing, automated continuous integration,
 * and zero-dependency database seeding without requiring active web server or WordPress install.
 * REF: https://github.com/izhubs/iz-wp-lite/docs/gonatour_data_model.json
 *
 * @package           IZTourEngine\Scripts
 * @author            Seed Data & Integration QA Engineer
 * @license           GPL-2.0-or-later
 */

declare(strict_types=1);

namespace IZTourEngine\Seeds;

/**
 * Class Gonatour_Seed_Importer
 *
 * Encapsulates data compilation, JSON schema compliance, SQL generation,
 * and simulation engines for the WordPress tour platform.
 */
class Gonatour_Seed_Importer
{
    private string $data_model_path;
    private string $root_dir;
    private array $data_model = [];

    /**
     * Constructor.
     *
     * WHY: Initializes target filesystem paths relative to repository root.
     *
     * @param string|null $data_model_path Optional override path to gonatour_data_model.json.
     * @param string|null $root_dir        Optional override path to repository root.
     */
    public function __construct(?string $data_model_path = null, ?string $root_dir = null)
    {
        $this->root_dir = $root_dir ?: dirname(__DIR__);
        $this->data_model_path = $data_model_path ?: $this->root_dir . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'gonatour_data_model.json';
    }

    /**
     * Load and parse JSON data model specification.
     *
     * WHY: Validates file accessibility and JSON integrity before seeding starts.
     *
     * @return array
     * @throws \RuntimeException If file does not exist or JSON parsing fails.
     */
    public function load_model(): array
    {
        if (!file_exists($this->data_model_path)) {
            throw new \RuntimeException(sprintf('Data model file not found at: %s', $this->data_model_path));
        }

        $raw = file_get_contents($this->data_model_path);
        if ($raw === false) {
            throw new \RuntimeException(sprintf('Failed to read data model file: %s', $this->data_model_path));
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException(sprintf('JSON decoding failed: %s', json_last_error_msg()));
        }

        $this->data_model = $decoded;
        return $this->data_model;
    }

    /**
     * Compile complete dataset containing 10 tours, 4 guide posts, 4 service pages, and taxonomies.
     *
     * WHY: Centralizes business domain entity definitions conforming to Gonatour commercial standards.
     *
     * @return array Complete seed dictionary.
     */
    public function generate_dataset(): array
    {
        if (empty($this->data_model)) {
            $this->load_model();
        }

        $taxonomies = $this->build_taxonomies();
        $tours      = $this->build_tours();
        $posts      = $this->build_guide_posts();
        $pages      = $this->build_service_pages();

        return [
            'meta' => [
                'generator'    => 'Gonatour_Seed_Importer v1.0.0',
                'schema'       => 'gonatour_data_model.json',
                'created_at'   => '2026-09-23T22:15:00+07:00',
                'tour_count'   => count($tours),
                'post_count'   => count($posts),
                'page_count'   => count($pages),
                'tax_counts'   => [
                    'tour_type'        => count($taxonomies['tour_type']),
                    'tour_destination' => count($taxonomies['tour_destination']),
                    'category'         => count($taxonomies['category']),
                ],
            ],
            'taxonomies' => $taxonomies,
            'tours'      => $tours,
            'posts'      => $posts,
            'pages'      => $pages,
        ];
    }

    /**
     * Build taxonomy tree definitions.
     *
     * WHY: Provides hierarchical structure for multi-level destination routing (Region -> City)
     * and tour classification.
     *
     * @return array
     */
    private function build_taxonomies(): array
    {
        return [
            'tour_type' => [
                ['id' => 1, 'name' => 'Trong nước', 'slug' => 'trong-nuoc', 'description' => 'Tour du lịch các tuyến điểm nội địa Việt Nam.'],
                ['id' => 2, 'name' => 'Quốc tế', 'slug' => 'quoc-te', 'description' => 'Tour du lịch khám phá các quốc gia trên thế giới.'],
                ['id' => 3, 'name' => 'Combo Du Lịch', 'slug' => 'combo-du-lich', 'description' => 'Combo vé máy bay và khách sạn nghỉ dưỡng cao cấp.'],
                ['id' => 4, 'name' => 'Teambuilding', 'slug' => 'teambuilding', 'description' => 'Tour sự kiện đoàn thể và teambuilding doanh nghiệp.'],
            ],
            'tour_destination' => [
                // Top level regions
                ['id' => 10, 'name' => 'Miền Bắc', 'slug' => 'mien-bac', 'parent_id' => 0, 'parent_slug' => null, 'description' => 'Du lịch các tỉnh phía Bắc.'],
                ['id' => 11, 'name' => 'Miền Trung', 'slug' => 'mien-trung', 'parent_id' => 0, 'parent_slug' => null, 'description' => 'Du lịch miền Trung biển xanh cát trắng di sản.'],
                ['id' => 12, 'name' => 'Miền Nam', 'slug' => 'mien-nam', 'parent_id' => 0, 'parent_slug' => null, 'description' => 'Du lịch Nam Bộ và đồng bằng sông Cửu Long.'],
                ['id' => 13, 'name' => 'Đông Nam Á', 'slug' => 'dong-nam-a', 'parent_id' => 0, 'parent_slug' => null, 'description' => 'Du lịch các nước ASEAN.'],
                ['id' => 14, 'name' => 'Châu Á', 'slug' => 'chau-a', 'parent_id' => 0, 'parent_slug' => null, 'description' => 'Du lịch Đông Bắc Á và các quốc gia Châu Á.'],
                ['id' => 15, 'name' => 'Châu Âu', 'slug' => 'chau-au', 'parent_id' => 0, 'parent_slug' => null, 'description' => 'Du lịch các quốc gia liên minh Châu Âu và Schengen.'],

                // Child destinations
                ['id' => 20, 'name' => 'Sapa', 'slug' => 'sapa', 'parent_id' => 10, 'parent_slug' => 'mien-bac', 'description' => 'Thị trấn trong sương và đỉnh Fansipan.'],
                ['id' => 21, 'name' => 'Nha Trang', 'slug' => 'nha-trang', 'parent_id' => 11, 'parent_slug' => 'mien-trung', 'description' => 'Thành phố biển vịnh Nha Trang.'],
                ['id' => 22, 'name' => 'Đà Nẵng', 'slug' => 'da-nang', 'parent_id' => 11, 'parent_slug' => 'mien-trung', 'description' => 'Thành phố đáng sống và cung đường di sản.'],
                ['id' => 23, 'name' => 'Phú Quốc', 'slug' => 'phu-quoc', 'parent_id' => 12, 'parent_slug' => 'mien-nam', 'description' => 'Đảo Ngọc Phú Quốc thiên đường nghỉ dưỡng.'],
                ['id' => 24, 'name' => 'Miền Tây', 'slug' => 'mien-tay', 'parent_id' => 12, 'parent_slug' => 'mien-nam', 'description' => 'Sông nước miệt vườn Cần Thơ - Mỹ Tho.'],
                ['id' => 25, 'name' => 'Thái Lan', 'slug' => 'thai-lan', 'parent_id' => 13, 'parent_slug' => 'dong-nam-a', 'description' => 'Xứ sở chùa Vàng Bangkok - Pattaya.'],
                ['id' => 26, 'name' => 'Singapore', 'slug' => 'singapore', 'parent_id' => 13, 'parent_slug' => 'dong-nam-a', 'description' => 'Đảo quốc sư tử xanh sạch hiện đại.'],
                ['id' => 27, 'name' => 'Nhật Bản', 'slug' => 'nhat-ban', 'parent_id' => 14, 'parent_slug' => 'chau-a', 'description' => 'Xứ sở hoa anh đào Tokyo - Phú Sĩ - Kyoto - Osaka.'],
                ['id' => 28, 'name' => 'Hàn Quốc', 'slug' => 'han-quoc', 'parent_id' => 14, 'parent_slug' => 'chau-a', 'description' => 'Xứ sở kim chi Seoul - Nami - Everland.'],
                ['id' => 29, 'name' => 'Pháp - Thụy Sỹ - Ý', 'slug' => 'phap-thuy-sy-y', 'parent_id' => 15, 'parent_slug' => 'chau-au', 'description' => 'Hành trình 4 nước Tây Âu kinh điển.'],
            ],
            'category' => [
                ['id' => 1, 'name' => 'Cẩm nang du lịch', 'slug' => 'cam-nang-du-lich', 'description' => 'Kinh nghiệm, mẹo vặt và hướng dẫn chuẩn bị hành trình du lịch.'],
                ['id' => 2, 'name' => 'Tin tức & Khuyến mãi', 'slug' => 'tin-tuc-khuyen-mai', 'description' => 'Cập nhật ưu đãi tour và thông tin hàng không mới nhất.'],
            ],
        ];
    }

    /**
     * Build the 10 production-quality tour records specified in user requirements.
     *
     * WHY: Generates normalized models matching gonatour_data_model.json schema with
     * precise financial matrices, multi-day itineraries, and seat inventories.
     *
     * @return array
     */
    private function build_tours(): array
    {
        return [
            // Tour 1: Nha Trang 4N3D
            [
                'id'                      => 101,
                'title'                   => 'Tour Du Lịch Nha Trang 4N3D: Dốc Lết - Đảo Khỉ - Tháp Bà Ponagar',
                'tour_code'               => 'GNT4NHV-0112',
                'slug'                    => 'tour-du-lich-nha-trang-GNT4NHV-0112',
                'market_type'             => 1, // Domestic
                'duration_days'           => 4,
                'duration_nights'         => 3,
                'duration_label'          => '4N3D (4 Ngày 3 Đêm)',
                'departure_city'          => 'TP. Hồ Chí Minh',
                'destination_primary'     => 'Nha Trang',
                'destination_country'     => 'Việt Nam',
                'destination_route'       => 'Sài Gòn - Nha Trang - Dốc Lết - Hòn Lao Đảo Khỉ',
                'transportation'          => 'Xe du lịch cao cấp đời mới, Tàu du lịch vượt biển',
                'hotel_stars'             => '3* - 4*',
                'adult_price'             => 3197000,
                'child_price'             => 1990000,
                'infant_price'            => 0,
                'single_room_supplement'  => 1200000,
                'tour_status'             => 'available',
                'tour_type_slug'          => 'trong-nuoc',
                'destination_slug'        => 'nha-trang',
                'departures'              => [
                    [
                        'departure_id'    => 3871,
                        'departure_date'  => '2026-10-15',
                        'return_date'     => '2026-10-18',
                        'day_of_week'     => 'Thứ 5',
                        'duration_days'   => 4,
                        'max_seats'       => 25,
                        'remaining_seats' => 12,
                        'price'           => 3197000,
                        'status'          => 'available',
                    ],
                    [
                        'departure_id'    => 3872,
                        'departure_date'  => '2026-10-22',
                        'return_date'     => '2026-10-25',
                        'day_of_week'     => 'Thứ 5',
                        'duration_days'   => 4,
                        'max_seats'       => 25,
                        'remaining_seats' => 8,
                        'price'           => 3197000,
                        'status'          => 'limited',
                    ],
                    [
                        'departure_id'    => 3873,
                        'departure_date'  => '2026-11-05',
                        'return_date'     => '2026-11-08',
                        'day_of_week'     => 'Thứ 5',
                        'duration_days'   => 4,
                        'max_seats'       => 25,
                        'remaining_seats' => 20,
                        'price'           => 3197000,
                        'status'          => 'available',
                    ],
                ],
                'highlights'              => [
                    'Chiêm ngưỡng cung đường biển Hòn Chồng - Núi Cô Tiên hoang sơ thơ mộng.',
                    'Tắm biển tại Dốc Lết với bờ cát mịn thoải dài và làn nước trong xanh màu ngọc bích.',
                    'Khám phá văn hóa Chămpa linh thiêng tại quần thể Tháp Bà Ponagar ngàn năm tuổi.',
                    'Tham quan Khu du lịch sinh thái Đảo Khỉ Hòn Lao, thưởng thức xiếc thú đặc sắc.',
                ],
                'daily_itinerary'         => [
                    [
                        'day'           => 1,
                        'title'         => 'NGÀY 1: TP.HCM – PHAN THIẾT – NHA TRANG (Ăn sáng, trưa, tối)',
                        'meals'         => ['summary' => 'Ăn sáng, trưa, tối', 'breakfast' => true, 'lunch' => true, 'dinner' => true],
                        'desc'          => 'Sáng 05h30 xe và HDV đón đoàn tại điểm hẹn trung tâm TP.HCM khởi hành đi Nha Trang. Đoàn dùng bữa sáng tại Đồng Nai, trưa dừng chân dùng cơm tại Cà Ná ngắm biển tuyệt đẹp. Chiều đến Nha Trang, nhận phòng khách sạn 3-4 sao nghỉ ngơi. Tối thưởng thức ẩm thực phố biển, tự do dạo chợ đêm Nha Trang.',
                        'accommodation' => 'Khách sạn 3-4 sao trung tâm Nha Trang',
                        'transport'     => 'Xe du lịch máy lạnh 45 chỗ',
                    ],
                    [
                        'day'           => 2,
                        'title'         => 'NGÀY 2: BÃI BIỂN DỐC LẾT – THÁP BÀ PONAGAR – SUỐI KHOÁNG NÓNG (Ăn sáng, trưa, tối)',
                        'meals'         => ['summary' => 'Ăn sáng, trưa, tối', 'breakfast' => true, 'lunch' => true, 'dinner' => true],
                        'desc'          => 'Sau bữa buffet sáng, xe đưa đoàn đến Bãi biển Dốc Lết, tự do tắm biển và thưởng thức hải sản tươi sống do ngư dân đánh bắt. Chiều viếng Tháp Bà Ponagar - kiệt tác kiến trúc văn hóa Chăm. Tham quan trung tâm bùn khoáng nóng I-Resort thư giãn hồi phục năng lượng. Dùng bữa tối đặc sản nem nướng Ninh Hòa.',
                        'accommodation' => 'Khách sạn 3-4 sao trung tâm Nha Trang',
                        'transport'     => 'Xe du lịch máy lạnh',
                    ],
                    [
                        'day'           => 3,
                        'title'         => 'NGÀY 3: KHÁM PHÁ ĐẢO KHỈ HÒN LAO – CHÙA LONG SƠN (Ăn sáng, trưa)',
                        'meals'         => ['summary' => 'Ăn sáng, trưa (tối tự túc)', 'breakfast' => true, 'lunch' => true, 'dinner' => false],
                        'desc'          => 'Đoàn đến bến tàu Đá Chồng lên tàu vượt vịnh Nha Phu đến KDL Đảo Khỉ Hòn Lao. Tự do tắm biển, giao lưu cùng hơn 1.200 chú khỉ tự nhiên. Dùng cơm trưa tại nhà hàng trên đảo. Chiều viếng Chùa Long Sơn chiêm bái tượng Kim Thân Phật Tổ cao 24m. Tối tự do khám phá ẩm thực đêm Nha Trang.',
                        'accommodation' => 'Khách sạn 3-4 sao trung tâm Nha Trang',
                        'transport'     => 'Tàu du lịch + Xe du lịch',
                    ],
                    [
                        'day'           => 4,
                        'title'         => 'NGÀY 4: CHỢ ĐẦM – VƯỜN NHO PHAN RANG – TP.HCM (Ăn sáng, trưa)',
                        'meals'         => ['summary' => 'Ăn sáng, trưa', 'breakfast' => true, 'lunch' => true, 'dinner' => false],
                        'desc'          => 'Đoàn ăn sáng, làm thủ tục trả phòng. Ghé Chợ Đầm mua đặc sản yến sào, mực rim me, cá ngựa. Khởi hành về Sài Gòn, ghé Phan Rang tham quan vườn nho bạt ngàn, thưởng thức rượu nho mật nho miễn phí. Chiều tối về đến TP.HCM, kết thúc chương trình du lịch.',
                        'accommodation' => 'Kết thúc chương trình',
                        'transport'     => 'Xe du lịch máy lạnh 45 chỗ',
                    ],
                ],
                'inclusions'              => [
                    'Xe du lịch máy lạnh 45 chỗ đời mới chất lượng cao phục vụ suốt tuyến.',
                    'Lưu trú: 3 đêm khách sạn 3-4 sao tại Nha Trang (2-3 khách/phòng).',
                    'Ăn uống theo chương trình: 4 bữa sáng + 6 bữa chính tiêu chuẩn 150.000đ/suất.',
                    'Vé vào cổng các điểm tham quan: Tháp Bà Ponagar, vé tàu Đảo Khỉ Hòn Lao.',
                    'Bảo hiểm du lịch nội địa mức đền bù tối đa 30.000.000 VND/người/vụ.',
                    'Nước suối tinh khiết 1 chai/người/ngày, nón du lịch cao cấp.',
                ],
                'exclusions'              => [
                    'Chi phí cá nhân: giặt ủi, điện thoại, nước uống phát sinh ngoài chương trình.',
                    'Vé tắm bùn khoáng nóng I-Resort, vé cáp treo VinWonders.',
                    'Bữa tối ngày 3 để du khách tự do thưởng thức ẩm thực địa phương.',
                    'Thuế VAT 10% (nếu có nhu cầu xuất hóa đơn công ty).',
                ],
                'cancellation_terms'      => [
                    ['timeframe' => 'Ngay sau khi đăng ký cọc tour', 'penalty_rate' => '50% số tiền cọc'],
                    ['timeframe' => 'Trước 7 ngày khởi hành', 'penalty_rate' => '50% tổng giá tour'],
                    ['timeframe' => 'Trước 3-6 ngày khởi hành', 'penalty_rate' => '75% tổng giá tour'],
                    ['timeframe' => 'Trong vòng 48h hoặc vắng mặt', 'penalty_rate' => '100% tổng giá tour'],
                ],
            ],

            // Tour 2: Thái Lan 5N4D Bangkok - Pattaya
            [
                'id'                      => 102,
                'title'                   => 'Tour Thái Lan 5N4D: Bangkok - Pattaya - Alcazar Show - Baiyoke Sky',
                'tour_code'               => 'TL5NTAL-3009',
                'slug'                    => 'tour-thai-lan-bangkok-pattaya-TL5NTAL-3009',
                'market_type'             => 2, // International
                'duration_days'           => 5,
                'duration_nights'         => 4,
                'duration_label'          => '5N4D (5 Ngày 4 Đêm)',
                'departure_city'          => 'TP. Hồ Chí Minh (Sân bay Tân Sơn Nhất)',
                'destination_primary'     => 'Bangkok - Pattaya',
                'destination_country'     => 'Thái Lan',
                'destination_route'       => 'TP.HCM - Bangkok - Pattaya - Đảo Coral - Bangkok - TP.HCM',
                'transportation'          => 'Máy bay khứ hồi (SGN-BKK-SGN), Xe du lịch 45 chỗ đời mới, Cano cao tốc',
                'hotel_stars'             => '4* tiêu chuẩn Thái Lan',
                'adult_price'             => 7790000,
                'child_price'             => 6890000,
                'infant_price'            => 1500000,
                'single_room_supplement'  => 2400000,
                'tour_status'             => 'available',
                'tour_type_slug'          => 'quoc-te',
                'destination_slug'        => 'thai-lan',
                'departures'              => [
                    [
                        'departure_id'    => 3629,
                        'departure_date'  => '2026-10-10',
                        'return_date'     => '2026-10-14',
                        'day_of_week'     => 'Thứ 7',
                        'duration_days'   => 5,
                        'max_seats'       => 30,
                        'remaining_seats' => 14,
                        'price'           => 7790000,
                        'status'          => 'available',
                    ],
                    [
                        'departure_id'    => 3630,
                        'departure_date'  => '2026-10-24',
                        'return_date'     => '2026-10-28',
                        'day_of_week'     => 'Thứ 7',
                        'duration_days'   => 5,
                        'max_seats'       => 30,
                        'remaining_seats' => 6,
                        'price'           => 7790000,
                        'status'          => 'limited',
                    ],
                    [
                        'departure_id'    => 3631,
                        'departure_date'  => '2026-11-12',
                        'return_date'     => '2026-11-16',
                        'day_of_week'     => 'Thứ 5',
                        'duration_days'   => 5,
                        'max_seats'       => 30,
                        'remaining_seats' => 25,
                        'price'           => 7790000,
                        'status'          => 'available',
                    ],
                ],
                'highlights'              => [
                    'Tặng vé đại tiệc Buffet quốc tế tại tòa nhà cao nhất Thái Lan Baiyoke Sky 86 tầng.',
                    'Thưởng thức Cafe phủ vàng hoàng gia và bánh ngọt dát vàng độc đáo.',
                    'Tặng vé xem chương trình tạp kỹ quốc tế Alcazar Cabaret Show tráng lệ tại Pattaya.',
                    'Dạo thuyền trên dòng sông huyền thoại Chaophraya, viếng Chùa Phật Vàng Wat Traimit 5.5 tấn.',
                ],
                'daily_itinerary'         => [
                    [
                        'day'           => 1,
                        'title'         => 'NGÀY 1: TP.HCM – BANGKOK (Ăn Tối)',
                        'meals'         => ['summary' => 'Ăn Tối', 'breakfast' => false, 'lunch' => false, 'dinner' => true],
                        'desc'          => 'Trưởng đoàn đón khách tại ga quốc tế sân bay Tân Sơn Nhất đáp chuyến bay đi Bangkok. Xe và HDV địa phương đón đoàn đưa về nhận phòng khách sạn tại Bangkok. Dùng bữa tối tại nhà hàng lẩu Suki truyền thống.',
                        'accommodation' => 'Khách sạn 4 sao Bangkok (The Palazzo / Picnic)',
                        'transport'     => 'Máy bay khứ hồi + Xe du lịch',
                    ],
                    [
                        'day'           => 2,
                        'title'         => 'NGÀY 2: BANGKOK – LIGHTING ART MUSEUM – PATTAYA – CHÙA ĐẠI PHẬT (Ăn 3 bữa)',
                        'meals'         => ['summary' => 'Ăn 3 bữa', 'breakfast' => true, 'lunch' => true, 'dinner' => true],
                        'desc'          => 'Dùng buffet sáng, trả phòng. Khởi hành đi thành phố biển Pattaya. Tham quan Bảo tàng nghệ thuật ánh sáng Lighting Art Museum & Balloon Garden. Viếng Chùa Đại Phật Wat Phra Yai với tượng Phật cao 18m linh thiêng tọa lạc trên đỉnh đồi. Dùng bữa tối BBQ hải sản.',
                        'accommodation' => 'Khách sạn 4 sao Pattaya (Crystal Palace / Marble Garden)',
                        'transport'     => 'Xe du lịch 45 chỗ đời mới',
                    ],
                    [
                        'day'           => 3,
                        'title'         => 'NGÀY 3: ĐẢO CORAL – TRÂN BẢO PHẬT SƠN – ALCAZAR SHOW (Ăn 3 bữa)',
                        'meals'         => ['summary' => 'Ăn 3 bữa', 'breakfast' => true, 'lunch' => true, 'dinner' => true],
                        'desc'          => 'Đi cano cao tốc ra Đảo San Hô (Coral Island), tự do tắm biển hoặc tham gia nhảy dù biển, lặn ngắm san hô. Chiều viếng Trân Bảo Phật Sơn (Khao Chee Chan) ngọn núi khắc tượng Phật bằng 999kg vàng 24K. Tối thưởng thức đêm diễn tạp kỹ quốc tế hoành tráng Alcazar Show.',
                        'accommodation' => 'Khách sạn 4 sao Pattaya',
                        'transport'     => 'Cano cao tốc + Xe du lịch',
                    ],
                    [
                        'day'           => 4,
                        'title'         => 'NGÀY 4: PATTAYA – BAIYOKE SKY 86 TẦNG – TƯỢNG PHẬT 4 MẶT – BANGKOK (Ăn sáng, trưa)',
                        'meals'         => ['summary' => 'Ăn sáng, trưa (tối tự túc)', 'breakfast' => true, 'lunch' => true, 'dinner' => false],
                        'desc'          => 'Khởi hành về lại thủ đô Bangkok. Dùng đại tiệc Buffet quốc tế tại Baiyoke Sky 86 tầng ngắm toàn cảnh Bangkok. Chiều viếng Đền Erawan chiêm bái tượng Phật 4 Mặt linh hiển bậc nhất Thái Lan. Tự do mua sắm tại Central World, Big C Pratunam.',
                        'accommodation' => 'Khách sạn 4 sao Bangkok',
                        'transport'     => 'Xe du lịch 45 chỗ',
                    ],
                    [
                        'day'           => 5,
                        'title'         => 'NGÀY 5: DẠO THUYỀN CHAOPHRAYA – CHÙA PHẬT VÀNG – TP.HCM (Ăn sáng, trưa)',
                        'meals'         => ['summary' => 'Ăn sáng, trưa', 'breakfast' => true, 'lunch' => true, 'dinner' => false],
                        'desc'          => 'Dạo thuyền trên dòng sông Chaophraya huyền thoại ngắm cá nổi tâm linh. Viếng Wat Traimit chiêm ngưỡng tượng Phật bằng vàng nguyên khối nặng 5.5 tấn. Dùng bữa trưa trước khi ra sân bay Suvarnabhumi làm thủ tục bay về TP.HCM.',
                        'accommodation' => 'Kết thúc chương trình',
                        'transport'     => 'Máy bay + Xe du lịch',
                    ],
                ],
                'inclusions'              => [
                    'Vé máy bay khứ hồi SGN - BKK - SGN gồm 20kg ký gửi và 7kg xách tay.',
                    'Thuế phi trường 2 nước, phí an ninh sân bay và phụ thu nhiên liệu.',
                    'Khách sạn 4 sao tiêu chuẩn Thái Lan (2 khách/phòng, lẻ nam/nữ ngủ phòng 3).',
                    'Các bữa ăn theo chương trình gồm 01 bữa buffet xoay 86 tầng Baiyoke Sky và BBQ hải sản.',
                    'Vé tham quan tất cả các thắng cảnh theo lịch trình kèm show diễn Alcazar.',
                    'Bảo hiểm du lịch quốc tế bồi thường tối đa 220.000.000 VND.',
                    'Hướng dẫn viên tiếng Việt phục vụ chu đáo suốt tuyến.',
                ],
                'exclusions'              => [
                    'Tiền Tip quy định bắt buộc cho HDV và tài xế: 700.000 VND/khách/tour.',
                    'Phụ thu phòng đơn đối với khách có nhu cầu ở riêng: 2.400.000 VND.',
                    'Hóa đơn VAT và chi phí phát sinh cá nhân ngoài chương trình.',
                ],
                'cancellation_terms'      => [
                    ['timeframe' => 'Sau khi đặt cọc', 'penalty_rate' => '50% tiền cọc'],
                    ['timeframe' => 'Trước 20 ngày khởi hành', 'penalty_rate' => '50% tổng giá tour'],
                    ['timeframe' => 'Trước 15 ngày khởi hành', 'penalty_rate' => '75% tổng giá tour'],
                    ['timeframe' => 'Dưới 15 ngày khởi hành', 'penalty_rate' => '100% tổng giá tour'],
                ],
            ],

            // Tour 3: Đà Nẵng - Bà Nà - Hội An - Huế 4N3D
            [
                'id'                      => 103,
                'title'                   => 'Tour Đà Nẵng - Bà Nà Hills - Cầu Vàng - Phố Cổ Hội An - Cố Đô Huế 4N3D',
                'tour_code'               => 'GNT4DNH-2026',
                'slug'                    => 'tour-da-nang-ba-na-hoi-an-hue-GNT4DNH-2026',
                'market_type'             => 1,
                'duration_days'           => 4,
                'duration_nights'         => 3,
                'duration_label'          => '4N3D (4 Ngày 3 Đêm)',
                'departure_city'          => 'TP. Hồ Chí Minh / Hà Nội',
                'destination_primary'     => 'Đà Nẵng - Hội An - Huế',
                'destination_country'     => 'Việt Nam',
                'destination_route'       => 'Đà Nẵng - Bán đảo Sơn Trà - Ngũ Hành Sơn - Hội An - Bà Nà - Cố Đô Huế',
                'transportation'          => 'Vé máy bay khứ hồi, Xe ô tô du lịch đời mới chất lượng cao',
                'hotel_stars'             => '4* cao cấp',
                'adult_price'             => 4890000,
                'child_price'             => 3490000,
                'infant_price'            => 500000,
                'single_room_supplement'  => 1600000,
                'tour_status'             => 'available',
                'tour_type_slug'          => 'trong-nuoc',
                'destination_slug'        => 'da-nang',
                'departures'              => [
                    [
                        'departure_id'    => 4001,
                        'departure_date'  => '2026-10-08',
                        'return_date'     => '2026-10-11',
                        'day_of_week'     => 'Thứ 5',
                        'duration_days'   => 4,
                        'max_seats'       => 25,
                        'remaining_seats' => 10,
                        'price'           => 4890000,
                        'status'          => 'available',
                    ],
                    [
                        'departure_id'    => 4002,
                        'departure_date'  => '2026-10-29',
                        'return_date'     => '2026-11-01',
                        'day_of_week'     => 'Thứ 5',
                        'duration_days'   => 4,
                        'max_seats'       => 25,
                        'remaining_seats' => 5,
                        'price'           => 4890000,
                        'status'          => 'limited',
                    ],
                    [
                        'departure_id'    => 4003,
                        'departure_date'  => '2026-11-19',
                        'return_date'     => '2026-11-22',
                        'day_of_week'     => 'Thứ 5',
                        'duration_days'   => 4,
                        'max_seats'       => 25,
                        'remaining_seats' => 18,
                        'price'           => 4890000,
                        'status'          => 'available',
                    ],
                ],
                'highlights'              => [
                    'Chinh phục Cầu Vàng - Bàn Tay Phật khổng lồ nổi tiếng toàn cầu tại đỉnh Bà Nà.',
                    'Dạo bước trong không gian hoài niệm đèn lồng lung linh của Di sản văn hóa thế giới Phố Cổ Hội An.',
                    'Tham quan Đại Nội Kinh Thành Huế và Lăng Khải Định kiệt tác lăng tẩm triều Nguyễn.',
                    'Khám phá Danh thắng Ngũ Hành Sơn huyền ảo và Làng đá mỹ nghệ Non Nước.',
                ],
                'daily_itinerary'         => [
                    [
                        'day'           => 1,
                        'title'         => 'NGÀY 1: ĐÀ NẴNG – BÁN ĐẢO SƠN TRÀ – NGŨ HÀNH SƠN – HỘI AN (Ăn trưa, tối)',
                        'meals'         => ['summary' => 'Ăn trưa, tối', 'breakfast' => false, 'lunch' => true, 'dinner' => true],
                        'desc'          => 'Đón khách tại sân bay Đà Nẵng. Khởi hành tham quan Bán đảo Sơn Trà viếng Chùa Linh Ứng chiêm bái tượng Phật Bà cao 67m. Trưa dùng đặc sản bánh tráng cuốn thịt heo hai đầu da. Chiều tham quan Ngũ Hành Sơn, di chuyển sang Phố Cổ Hội An thả hoa đăng, thưởng thức cao lầu. Về Đà Nẵng nhận phòng khách sạn.',
                        'accommodation' => 'Khách sạn 4 sao biển Mỹ Khê Đà Nẵng',
                        'transport'     => 'Xe du lịch máy lạnh',
                    ],
                    [
                        'day'           => 2,
                        'title'         => 'NGÀY 2: SUN WORLD BÀ NÀ HILLS – CẦU VÀNG – BIỂN MỸ KHÊ (Ăn sáng, trưa, tối)',
                        'meals'         => ['summary' => 'Ăn sáng, trưa buffet, tối', 'breakfast' => true, 'lunch' => true, 'dinner' => true],
                        'desc'          => 'Ăn sáng buffet. Xe đưa đoàn đến Bà Nà Hills, đi cáp treo đạt 4 kỷ lục thế giới. Check-in Cầu Vàng nổi tiếng, Làng Pháp, Hầm rượu Debay, Vườn hoa Le Jardin. Dùng buffet trưa hơn 100 món tại nhà hàng Arapang. Chiều về lại thành phố tắm biển Mỹ Khê, ngắm Cầu Rồng phun lửa về đêm.',
                        'accommodation' => 'Khách sạn 4 sao biển Mỹ Khê Đà Nẵng',
                        'transport'     => 'Cáp treo + Xe du lịch',
                    ],
                    [
                        'day'           => 3,
                        'title'         => 'NGÀY 3: ĐÀ NẴNG – ĐÈO HẢI VÂN – CỐ ĐÔ HUẾ – ĐẠI NỘI (Ăn sáng, trưa, tối)',
                        'meals'         => ['summary' => 'Ăn sáng, trưa, tối ca Huế', 'breakfast' => true, 'lunch' => true, 'dinner' => true],
                        'desc'          => 'Đoàn khởi hành đi Huế xuyên hầm Hải Vân. Đến Huế, tham quan Đại Nội Hoàng Cung của 13 vị vua triều Nguyễn với Ngọ Môn, Điện Thái Hòa, Tử Cấm Thành. Chiều viếng Chùa Thiên Mụ cổ kính bên bờ sông Hương. Tối thưởng thức ca Huế trên thuyền rồng ngắm cầu Tràng Tiền.',
                        'accommodation' => 'Khách sạn 4 sao trung tâm Huế',
                        'transport'     => 'Thuyền Rồng + Xe du lịch',
                    ],
                    [
                        'day'           => 4,
                        'title'         => 'NGÀY 4: LĂNG KHẢI ĐỊNH – CHỢ ĐÔNG BA – TIỄN SÂN BAY (Ăn sáng, trưa)',
                        'meals'         => ['summary' => 'Ăn sáng, trưa', 'breakfast' => true, 'lunch' => true, 'dinner' => false],
                        'desc'          => 'Ăn sáng, tham quan Lăng Khải Định - đỉnh cao nghệ thuật khảm sành sứ độc nhất vô nhị. Ghé Chợ Đông Ba mua đặc sản mè xửng, tôm chua, trà cung đình. Trưa dùng bữa tại nhà hàng Huế trước khi xe đưa đoàn ra sân bay Đà Nẵng / Phú Bài làm thủ tục về lại.',
                        'accommodation' => 'Kết thúc chương trình',
                        'transport'     => 'Xe du lịch máy lạnh',
                    ],
                ],
                'inclusions'              => [
                    'Vé máy bay khứ hồi bao gồm 7kg xách tay và 20kg ký gửi.',
                    'Xe du lịch tiện nghi phục vụ trọn vẹn theo lộ trình.',
                    '3 đêm khách sạn 4 sao (2 người/phòng, lẻ bố trí phòng 3).',
                    'Vé cáp treo Bà Nà Hills và buffet trưa Arapang.',
                    'Vé tham quan tất cả các điểm di tích: Ngũ Hành Sơn, Hội An, Đại Nội, Khải Định.',
                    'Vé nghe ca Huế trên sông Hương và thả hoa đăng.',
                    'Bảo hiểm du lịch mức 50.000.000 VND.',
                ],
                'exclusions'              => [
                    'Chi phí phát sinh cá nhân, phòng đơn 1.600.000 VND, thuế VAT.',
                ],
                'cancellation_terms'      => [
                    ['timeframe' => 'Sau khi đặt cọc', 'penalty_rate' => '50% tiền cọc'],
                    ['timeframe' => 'Trước 10 ngày khởi hành', 'penalty_rate' => '50% tổng tour'],
                    ['timeframe' => 'Dưới 5 ngày', 'penalty_rate' => '100% tổng tour'],
                ],
            ],

            // Tour 4: Phú Quốc 3N2Đ Khám phá Đảo Ngọc
            [
                'id'                      => 104,
                'title'                   => 'Tour Phú Quốc 3N2Đ Khám Phá Đảo Ngọc - Grand World - Cáp Treo Hòn Thơm',
                'tour_code'               => 'GNT3PQ-2026',
                'slug'                    => 'tour-phu-quoc-3n2d-kham-pha-dao-ngoc-GNT3PQ-2026',
                'market_type'             => 1,
                'duration_days'           => 3,
                'duration_nights'         => 2,
                'duration_label'          => '3N2Đ (3 Ngày 2 Đêm)',
                'departure_city'          => 'TP. Hồ Chí Minh',
                'destination_primary'     => 'Phú Quốc',
                'destination_country'     => 'Việt Nam',
                'destination_route'       => 'Dương Đông - Grand World - Hòn Mây Rút - Hòn Móng Tay - Hòn Thơm',
                'transportation'          => 'Vé máy bay khứ hồi, Cano SB lặn biển, Xe du lịch máy lạnh',
                'hotel_stars'             => '4* resort biển',
                'adult_price'             => 3450000,
                'child_price'             => 2650000,
                'infant_price'            => 350000,
                'single_room_supplement'  => 1300000,
                'tour_status'             => 'available',
                'tour_type_slug'          => 'trong-nuoc',
                'destination_slug'        => 'phu-quoc',
                'departures'              => [
                    [
                        'departure_id'    => 4101,
                        'departure_date'  => '2026-10-16',
                        'return_date'     => '2026-10-18',
                        'day_of_week'     => 'Thứ 6',
                        'duration_days'   => 3,
                        'max_seats'       => 20,
                        'remaining_seats' => 7,
                        'price'           => 3450000,
                        'status'          => 'available',
                    ],
                    [
                        'departure_id'    => 4102,
                        'departure_date'  => '2026-11-06',
                        'return_date'     => '2026-11-08',
                        'day_of_week'     => 'Thứ 6',
                        'duration_days'   => 3,
                        'max_seats'       => 20,
                        'remaining_seats' => 12,
                        'price'           => 3450000,
                        'status'          => 'available',
                    ],
                    [
                        'departure_id'    => 4103,
                        'departure_date'  => '2026-11-20',
                        'return_date'     => '2026-11-22',
                        'day_of_week'     => 'Thứ 6',
                        'duration_days'   => 3,
                        'max_seats'       => 20,
                        'remaining_seats' => 4,
                        'price'           => 3450000,
                        'status'          => 'limited',
                    ],
                ],
                'highlights'              => [
                    'Trải nghiệm cáp treo 3 dây vượt biển Hòn Thơm dài nhất thế giới gần 7.899m.',
                    'Khám phá thiên đường giải trí Grand World - Thành phố không ngủ hoạt động 24/7.',
                    'Vi vu cano cao tốc tham quan 4 hòn đảo thiên đường Mây Rút, Gầm Ghì, Móng Tay.',
                    'Check-in Thị Trấn Hoàng Hôn Sunset Town và Cầu Hôn Kiss Bridge trứ danh.',
                ],
                'daily_itinerary'         => [
                    [
                        'day'           => 1,
                        'title'         => 'NGÀY 1: TP.HCM – PHÚ QUỐC – DINH CẬU – GRAND WORLD (Ăn trưa, tối)',
                        'meals'         => ['summary' => 'Ăn trưa, tối', 'breakfast' => false, 'lunch' => true, 'dinner' => true],
                        'desc'          => 'Đón khách tại sân bay Phú Quốc, ăn trưa với đặc sản gỏi cá trích trứ danh. Nhận phòng resort 4 sao nghỉ ngơi. Chiều viếng Dinh Cậu tâm linh, ngắm hoàng hôn biển Tây. Tối đến Grand World đi thuyền Gondola trên kênh đào Venice, xem show diễn thực cảnh Tinh Hoa Việt Nam.',
                        'accommodation' => 'Resort 4 sao Sunset Sanato / Novotel Phú Quốc',
                        'transport'     => 'Máy bay + Xe du lịch',
                    ],
                    [
                        'day'           => 2,
                        'title'         => 'NGÀY 2: CANO 4 ĐẢO – NGẮM SAN HÔ – CÁP TREO HÒN THƠM (Ăn 3 bữa)',
                        'meals'         => ['summary' => 'Ăn 3 bữa', 'breakfast' => true, 'lunch' => true, 'dinner' => true],
                        'desc'          => 'Lên cano cao tốc vượt sóng khám phá Hòn Móng Tay, Hòn Mây Rút Trong. Trải nghiệm lặn ngắm rạn san hô tự nhiên rực rỡ tại Hòn Gầm Ghì. Dùng bữa trưa hải sản trên đảo. Chiều trải nghiệm cáp treo vượt biển Hòn Thơm ngắm toàn cảnh quần đảo An Thới từ trên cao.',
                        'accommodation' => 'Resort 4 sao Phú Quốc',
                        'transport'     => 'Cano cao tốc + Cáp treo Hòn Thơm',
                    ],
                    [
                        'day'           => 3,
                        'title'         => 'NGÀY 3: NHÀ THÙNG NƯỚC MẮM – VƯỜN TIÊU – TIỄN SÂN BAY (Ăn sáng, trưa)',
                        'meals'         => ['summary' => 'Ăn sáng, trưa', 'breakfast' => true, 'lunch' => true, 'dinner' => false],
                        'desc'          => 'Ăn sáng, tắm biển sáng sớm. Tham quan nhà thùng nước mắm truyền thống Khải Hoàn hơn 40 độ đạm, vườn tiêu Suối Đá và cơ sở chế tác ngọc trai biển. Dùng bữa trưa trước khi xe đưa đoàn ra sân bay Phú Quốc đáp chuyến bay về lại.',
                        'accommodation' => 'Kết thúc chương trình',
                        'transport'     => 'Xe du lịch máy lạnh',
                    ],
                ],
                'inclusions'              => [
                    'Vé máy bay khứ hồi SGN - PQC - SGN.',
                    '2 đêm lưu trú tại resort 4 sao giáp biển.',
                    'Tour cano 4 đảo chuyên nghiệp bao gồm thiết bị lặn ống thở.',
                    'Vé cáp treo vượt biển Hòn Thơm 2 chiều.',
                    'Các bữa ăn theo chương trình tiêu chuẩn ẩm thực biển.',
                    'Bảo hiểm du lịch 30.000.000 VND.',
                ],
                'exclusions'              => [
                    'Chi phí cá nhân ngoài chương trình, vé show diễn Tinh Hoa Việt Nam, VAT.',
                ],
                'cancellation_terms'      => [
                    ['timeframe' => 'Sau khi đặt cọc', 'penalty_rate' => '50% tiền cọc'],
                    ['timeframe' => 'Trước 7 ngày', 'penalty_rate' => '50% tổng tour'],
                    ['timeframe' => 'Dưới 3 ngày', 'penalty_rate' => '100% tổng tour'],
                ],
            ],

            // Tour 5: Hà Nội - Sapa 3N2Đ Fansipan Cát Cát
            [
                'id'                      => 105,
                'title'                   => 'Tour Hà Nội - Sapa 3N2Đ: Chinh Phục Đỉnh Fansipan - Bản Cát Cát - Đèo Ô Quy Hồ',
                'tour_code'               => 'GNT3SP-2026',
                'slug'                    => 'tour-ha-noi-sapa-fansipan-cat-cat-GNT3SP-2026',
                'market_type'             => 1,
                'duration_days'           => 3,
                'duration_nights'         => 2,
                'duration_label'          => '3N2Đ (3 Ngày 2 Đêm)',
                'departure_city'          => 'Hà Nội',
                'destination_primary'     => 'Sapa',
                'destination_country'     => 'Việt Nam',
                'destination_route'       => 'Hà Nội - Cao tốc Nội Bài Lào Cai - Sapa - Fansipan - Bản Cát Cát - Ô Quy Hồ',
                'transportation'          => 'Xe Limousine Dcar cao cấp, Cáp treo Fansipan Legend',
                'hotel_stars'             => '3* - 4*',
                'adult_price'             => 2850000,
                'child_price'             => 1950000,
                'infant_price'            => 0,
                'single_room_supplement'  => 950000,
                'tour_status'             => 'available',
                'tour_type_slug'          => 'trong-nuoc',
                'destination_slug'        => 'sapa',
                'departures'              => [
                    [
                        'departure_id'    => 4201,
                        'departure_date'  => '2026-10-11',
                        'return_date'     => '2026-10-13',
                        'day_of_week'     => 'Chủ nhật',
                        'duration_days'   => 3,
                        'max_seats'       => 18,
                        'remaining_seats' => 9,
                        'price'           => 2850000,
                        'status'          => 'available',
                    ],
                    [
                        'departure_id'    => 4202,
                        'departure_date'  => '2026-10-25',
                        'return_date'     => '2026-10-27',
                        'day_of_week'     => 'Chủ nhật',
                        'duration_days'   => 3,
                        'max_seats'       => 18,
                        'remaining_seats' => 5,
                        'price'           => 2850000,
                        'status'          => 'limited',
                    ],
                    [
                        'departure_id'    => 4203,
                        'departure_date'  => '2026-11-08',
                        'return_date'     => '2026-11-10',
                        'day_of_week'     => 'Chủ nhật',
                        'duration_days'   => 3,
                        'max_seats'       => 18,
                        'remaining_seats' => 16,
                        'price'           => 2850000,
                        'status'          => 'available',
                    ],
                ],
                'highlights'              => [
                    'Chinh phục nóc nhà Đông Dương Fansipan cao 3.143m ngắm biển mây bồng bềnh.',
                    'Dạo bước qua những thửa ruộng bậc thang kỳ vĩ tại Bản Cát Cát của người H’Mông.',
                    'Ngắm hoàng hôn ngoạn mục tại Đèo Ô Quy Hồ - một trong tứ đại đỉnh đèo Tây Bắc.',
                    'Thưởng thức đặc sản lẩu cá hồi, cá tầm và thắng cố Sapa trong tiết trời se lạnh.',
                ],
                'daily_itinerary'         => [
                    [
                        'day'           => 1,
                        'title'         => 'NGÀY 1: HÀ NỘI – CAO TỐC LÀO CAI – SAPA – BẢN CÁT CÁT (Ăn trưa, tối)',
                        'meals'         => ['summary' => 'Ăn trưa, tối', 'breakfast' => false, 'lunch' => true, 'dinner' => true],
                        'desc'          => '06h30 xe Limousine đón quý khách tại phố cổ Hà Nội khởi hành đi Sapa theo cao tốc Nội Bài - Lào Cai êm thuận. 12h30 đến Sapa, nhận phòng khách sạn dùng cơm trưa. Chiều bách bộ xuống Bản Cát Cát tìm hiểu văn hóa dệt thổ cẩm người H’Mông, check-in thác Tiên Sa. Tối tự do khám phá Nhà thờ Đá và chợ tình Sapa.',
                        'accommodation' => 'Khách sạn 3-4 sao trung tâm thị xã Sapa',
                        'transport'     => 'Xe Limousine VIP',
                    ],
                    [
                        'day'           => 2,
                        'title'         => 'NGÀY 2: CHINH PHỤC ĐỈNH FANSIPAN 3.143M – MOANA SAPA (Ăn 3 bữa)',
                        'meals'         => ['summary' => 'Ăn 3 bữa', 'breakfast' => true, 'lunch' => true, 'dinner' => true],
                        'desc'          => 'Ăn sáng tại khách sạn. Xe đưa đoàn đến ga cáp treo Fansipan Legend, trải nghiệm hệ thống cáp treo 3 dây hiện đại nhất thế giới. Chiêm bái Đại tượng Phật A Di Đà bằng đồng lớn nhất Việt Nam, chạm tay vào cột mốc Fansipan 3.143m. Chiều check-in khu sinh thái Moana Sapa với tượng Moana và hồ vô cực.',
                        'accommodation' => 'Khách sạn 3-4 sao Sapa',
                        'transport'     => 'Cáp treo Fansipan + Xe ô tô',
                    ],
                    [
                        'day'           => 3,
                        'title'         => 'NGÀY 3: ĐÈO Ô QUY HỒ – CỔNG TRỜI – LÀO CAI – HÀ NỘI (Ăn sáng, trưa)',
                        'meals'         => ['summary' => 'Ăn sáng, trưa', 'breakfast' => true, 'lunch' => true, 'dinner' => false],
                        'desc'          => 'Đoàn dùng buffet sáng, trả phòng. Khởi hành đi Đèo Ô Quy Hồ ngắm toàn cảnh dãy Hoàng Liên Sơn hùng vĩ từ Cổng Trời. Thưởng thức trứng nướng, cơm lam tại đỉnh đèo. Trưa ăn cơm tại nhà hàng trước khi lên xe Limousine về lại Hà Nội. 19h00 về đến điểm đón ban đầu.',
                        'accommodation' => 'Kết thúc chương trình',
                        'transport'     => 'Xe Limousine VIP',
                    ],
                ],
                'inclusions'              => [
                    'Xe Limousine khứ hồi Hà Nội - Sapa - Hà Nội ghế ngả massage cao cấp.',
                    'Lưu trú 2 đêm khách sạn 3-4 sao trung tâm Sapa.',
                    'Vé cáp treo khứ hồi Fansipan Legend và tàu hỏa leo núi Mường Hoa.',
                    'Vé tham quan Bản Cát Cát, khu sinh thái Moana.',
                    'Các bữa ăn theo chương trình với ẩm thực Tây Bắc tươi ngon.',
                    'Bảo hiểm du lịch 40.000.000 VND.',
                ],
                'exclusions'              => [
                    'Vé tàu hỏa leo đỉnh Fansipan chặng cuối (150.000đ), chi phí cá nhân, VAT.',
                ],
                'cancellation_terms'      => [
                    ['timeframe' => 'Sau khi đặt cọc', 'penalty_rate' => '30% tiền cọc'],
                    ['timeframe' => 'Trước 5 ngày', 'penalty_rate' => '50% tổng giá tour'],
                    ['timeframe' => 'Dưới 48h', 'penalty_rate' => '100% tổng giá tour'],
                ],
            ],

            // Tour 6: Miền Tây 2N1Đ Mỹ Tho - Cần Thơ - Chợ Nổi
            [
                'id'                      => 106,
                'title'                   => 'Tour Miền Tây 2N1Đ: Mỹ Tho - Bến Tre - Cần Thơ - Chợ Nổi Cái Răng',
                'tour_code'               => 'GNT2MT-2026',
                'slug'                    => 'tour-mien-tay-my-tho-can-tho-cho-noi-GNT2MT-2026',
                'market_type'             => 1,
                'duration_days'           => 2,
                'duration_nights'         => 1,
                'duration_label'          => '2N1Đ (2 Ngày 1 Đêm)',
                'departure_city'          => 'TP. Hồ Chí Minh',
                'destination_primary'     => 'Mỹ Tho - Cần Thơ',
                'destination_country'     => 'Việt Nam',
                'destination_route'       => 'Sài Gòn - Mỹ Tho - Cù Lao Thới Sơn - Bến Tre - Cần Thơ - Chợ Nổi Cái Răng',
                'transportation'          => 'Xe du lịch máy lạnh đời mới, Xuồng chèo ba lá rợp bóng dừa nước, Tàu du lịch sông Tiền',
                'hotel_stars'             => '3* - 4*',
                'adult_price'             => 1650000,
                'child_price'             => 1150000,
                'infant_price'            => 0,
                'single_room_supplement'  => 450000,
                'tour_status'             => 'available',
                'tour_type_slug'          => 'trong-nuoc',
                'destination_slug'        => 'mien-tay',
                'departures'              => [
                    [
                        'departure_id'    => 4301,
                        'departure_date'  => '2026-10-17',
                        'return_date'     => '2026-10-18',
                        'day_of_week'     => 'Thứ 7',
                        'duration_days'   => 2,
                        'max_seats'       => 30,
                        'remaining_seats' => 15,
                        'price'           => 1650000,
                        'status'          => 'available',
                    ],
                    [
                        'departure_id'    => 4302,
                        'departure_date'  => '2026-10-31',
                        'return_date'     => '2026-11-01',
                        'day_of_week'     => 'Thứ 7',
                        'duration_days'   => 2,
                        'max_seats'       => 30,
                        'remaining_seats' => 11,
                        'price'           => 1650000,
                        'status'          => 'available',
                    ],
                    [
                        'departure_id'    => 4303,
                        'departure_date'  => '2026-11-14',
                        'return_date'     => '2026-11-15',
                        'day_of_week'     => 'Thứ 7',
                        'duration_days'   => 2,
                        'max_seats'       => 30,
                        'remaining_seats' => 22,
                        'price'           => 1650000,
                        'status'          => 'available',
                    ],
                ],
                'highlights'              => [
                    'Khám phá Chợ Nổi Cái Răng - di sản văn hóa phi vật thể quốc gia trên dòng sông Hậu.',
                    'Đi xuồng ba lá len lỏi trong rạch dừa nước rợp bóng mát mẻ tại Bến Tre.',
                    'Thưởng thức đờn ca tài tử Nam Bộ và trái cây miệt vườn tươi rói vừa hái trên cành.',
                    'Dạo bến Ninh Kiều lung linh ánh đèn và thưởng thức bữa tối trên du thuyền Cần Thơ.',
                ],
                'daily_itinerary'         => [
                    [
                        'day'           => 1,
                        'title'         => 'NGÀY 1: TP.HCM – MỸ THO – BẾN TRE – CẦN THƠ (Ăn sáng, trưa, tối)',
                        'meals'         => ['summary' => 'Ăn sáng, trưa cá tai tượng, tối du thuyền', 'breakfast' => true, 'lunch' => true, 'dinner' => true],
                        'desc'          => '07h00 đón khách tại TP.HCM đi Mỹ Tho. Thăm Chùa Vĩnh Tràng cổ kính. Lên thuyền du ngoạn sông Tiền ngắm tứ linh Long - Lân - Quy - Phụng. Sang Bến Tre đi xe ngựa trên đường làng, chèo xuồng ba lá qua rạch dừa nước. Dùng cơm trưa cá tai tượng chiên xù. Chiều về Cần Thơ nhận phòng. Tối lên du thuyền Ninh Kiều thưởng thức ẩm thực Tây Đô.',
                        'accommodation' => 'Khách sạn 3-4 sao trung tâm Cần Thơ',
                        'transport'     => 'Xe du lịch + Thuyền du lịch sông Tiền + Xuồng ba lá',
                    ],
                    [
                        'day'           => 2,
                        'title'         => 'NGÀY 2: CHỢ NỔI CÁI RĂNG – LÒ HỦ TIẾU – VƯỜN TRÁI CÂY – TP.HCM (Ăn sáng, trưa)',
                        'meals'         => ['summary' => 'Ăn sáng, trưa', 'breakfast' => true, 'lunch' => true, 'dinner' => false],
                        'desc'          => '05h30 sáng lên thuyền đi Chợ Nổi Cái Răng ngắm cảnh mua bán nhộn nhịp trên sông qua cây bẹo đặc trưng. Thưởng thức hủ tiếu tô và cafe nổi trên thuyền. Ghé lò hủ tiếu truyền thống trải nghiệm làm pizza hủ tiếu. Thăm miệt vườn Mỹ Khánh rợp bóng trái cây. Chiều khởi hành về lại TP.HCM, kết thúc hành trình 2N1Đ ý nghĩa.',
                        'accommodation' => 'Kết thúc chương trình',
                        'transport'     => 'Thuyền sông Hậu + Xe du lịch',
                    ],
                ],
                'inclusions'              => [
                    'Xe du lịch chất lượng cao đưa đón trọn gói.',
                    'Khách sạn 3-4 sao trung tâm Cần Thơ giáp bến Ninh Kiều.',
                    'Thuyền du lịch Mỹ Tho, thuyền tham quan Chợ Nổi Cái Răng, xuồng chèo ba lá.',
                    'Vé tham quan tất cả các điểm trong lộ trình.',
                    'Bữa ăn tối sang trọng trên Du thuyền Cần Thơ.',
                    'Bảo hiểm du lịch 30.000.000 VND.',
                ],
                'exclusions'              => [
                    'Chi phí cá nhân, đồ uống gọi thêm ngoài thực đơn, VAT.',
                ],
                'cancellation_terms'      => [
                    ['timeframe' => 'Trước 5 ngày khởi hành', 'penalty_rate' => 'Miễn phí hủy'],
                    ['timeframe' => 'Trước 2-4 ngày', 'penalty_rate' => '50% giá tour'],
                    ['timeframe' => 'Trong vòng 24h', 'penalty_rate' => '100% giá tour'],
                ],
            ],

            // Tour 7: Singapore - Malaysia 4N3D
            [
                'id'                      => 107,
                'title'                   => 'Tour Singapore - Malaysia 4N3D: Marina Bay Sands - Sentosa - Malacca - Genting',
                'tour_code'               => 'GSM4NTN-2409',
                'slug'                    => 'tour-singapore-malaysia-4n3d-GSM4NTN-2409',
                'market_type'             => 2,
                'duration_days'           => 4,
                'duration_nights'         => 3,
                'duration_label'          => '4N3D (4 Ngày 3 Đêm)',
                'departure_city'          => 'TP. Hồ Chí Minh',
                'destination_primary'     => 'Singapore & Malaysia',
                'destination_country'     => 'Singapore / Malaysia',
                'destination_route'       => 'TP.HCM - Singapore - Đảo Sentosa - Malacca - Kuala Lumpur - Genting - TP.HCM',
                'transportation'          => 'Vé máy bay khứ hồi (Scoot / Vietnam Airlines), Xe du lịch VIP liên tuyến quốc tế',
                'hotel_stars'             => '3* - 4*',
                'adult_price'             => 9390000,
                'child_price'             => 8190000,
                'infant_price'            => 2500000,
                'single_room_supplement'  => 3100000,
                'tour_status'             => 'available',
                'tour_type_slug'          => 'quoc-te',
                'destination_slug'        => 'singapore',
                'departures'              => [
                    [
                        'departure_id'    => 4401,
                        'departure_date'  => '2026-10-14',
                        'return_date'     => '2026-10-17',
                        'day_of_week'     => 'Thứ 4',
                        'duration_days'   => 4,
                        'max_seats'       => 25,
                        'remaining_seats' => 8,
                        'price'           => 9390000,
                        'status'          => 'limited',
                    ],
                    [
                        'departure_id'    => 4402,
                        'departure_date'  => '2026-11-04',
                        'return_date'     => '2026-11-07',
                        'day_of_week'     => 'Thứ 4',
                        'duration_days'   => 4,
                        'max_seats'       => 25,
                        'remaining_seats' => 15,
                        'price'           => 9390000,
                        'status'          => 'available',
                    ],
                    [
                        'departure_id'    => 4403,
                        'departure_date'  => '2026-11-25',
                        'return_date'     => '2026-11-28',
                        'day_of_week'     => 'Thứ 4',
                        'duration_days'   => 4,
                        'max_seats'       => 25,
                        'remaining_seats' => 19,
                        'price'           => 9390000,
                        'status'          => 'available',
                    ],
                ],
                'highlights'              => [
                    'Khám phá kỳ quan kiến trúc Gardens by the Bay và cụm siêu cây Supertree Grove khổng lồ.',
                    'Check-in biểu tượng sư tử biển Merlion Park và tổ hợp nghỉ dưỡng đẳng cấp Marina Bay Sands.',
                    'Dạo bước trong khu phố cổ Di sản thế giới Malacca đậm đà phong cách Bồ Đào Nha - Hà Lan.',
                    'Trải nghiệm cáp treo lên cao nguyên Genting và vui chơi tại sòng bài casino hợp pháp hàng đầu Châu Á.',
                ],
                'daily_itinerary'         => [
                    [
                        'day'           => 1,
                        'title'         => 'NGÀY 1: TP.HCM – SINGAPORE – GARDENS BY THE BAY – MARINA BAY (Ăn trưa, tối)',
                        'meals'         => ['summary' => 'Ăn trưa, tối', 'breakfast' => false, 'lunch' => true, 'dinner' => true],
                        'desc'          => 'Trưởng đoàn đón khách tại ga quốc tế Tân Sơn Nhất đáp chuyến bay đi Singapore. Đến sân bay Changi, tham quan Thác nước Jewel Changi kỳ vĩ. Check-in Công viên Sư tử biển Merlion, Nhà hát Trái Sầu Riêng Esplanade. Chiều tham quan Gardens by the Bay. Tối thưởng thức show nhạc nước Spectra Light Show tại vịnh Marina Bay.',
                        'accommodation' => 'Khách sạn 3-4 sao trung tâm Singapore (Ibis / Oxford)',
                        'transport'     => 'Máy bay + Xe du lịch quốc tế',
                    ],
                    [
                        'day'           => 2,
                        'title'         => 'NGÀY 2: SINGAPORE – ĐẢO SENTOSA – CỬA KHẨU TUAS – PHỐ CỔ MALACCA (Ăn 3 bữa)',
                        'meals'         => ['summary' => 'Ăn 3 bữa', 'breakfast' => true, 'lunch' => true, 'dinner' => true],
                        'desc'          => 'Ăn sáng, xe đưa đoàn ra đảo Sentosa tham quan quả cầu Universal Studios, sòng bài Resorts World. Trưa di chuyển qua cửa khẩu Tuas nhập cảnh Malaysia. Khởi hành về thành phố cổ Malacca. Tham quan Quảng trường Hà Lan, Pháo đài cổ A Famosa, Nhà thờ Thánh Phaolô, dạo phố cổ Jonker Walk.',
                        'accommodation' => 'Khách sạn 4 sao Malacca (Heritage / Imperial)',
                        'transport'     => 'Xe du lịch liên tuyến',
                    ],
                    [
                        'day'           => 3,
                        'title'         => 'NGÀY 3: MALACCA – KUALA LUMPUR – CAO NGUYÊN GENTING – ĐỘNG BATU (Ăn 3 bữa)',
                        'meals'         => ['summary' => 'Ăn 3 bữa', 'breakfast' => true, 'lunch' => true, 'dinner' => true],
                        'desc'          => 'Khởi hành đi thủ đô Kuala Lumpur. Tham quan Động Batu với tượng thần Murugan mạ vàng cao 42.7m và 272 bậc thang rực rỡ sắc màu. Đi cáp treo chinh phục Cao nguyên giải trí Genting ở độ cao 2.000m mát mẻ. Chiều về lại Kuala Lumpur chiêm ngưỡng Tháp Đôi Petronas rực sáng về đêm.',
                        'accommodation' => 'Khách sạn 4 sao Kuala Lumpur (Cosmo / Swiss Garden)',
                        'transport'     => 'Cáp treo Genting + Xe du lịch',
                    ],
                    [
                        'day'           => 4,
                        'title'         => 'NGÀY 4: HOÀNG GIA MALAYSIA – QUẢNG TRƯỜNG ĐỘC LẬP – TP.HCM (Ăn sáng, trưa)',
                        'meals'         => ['summary' => 'Ăn sáng, trưa', 'breakfast' => true, 'lunch' => true, 'dinner' => false],
                        'desc'          => 'Ăn sáng buffet. Tham quan Cung điện Hoàng Gia Istana Negara nguy nga, Quảng trường Độc Lập Merdeka và Đài tưởng niệm Quốc Gia. Mua sắm đặc sản socola Beryl’s nổi tiếng thế giới. Sau bữa trưa, xe đưa đoàn ra sân bay quốc tế KLIA làm thủ tục bay về TP.HCM.',
                        'accommodation' => 'Kết thúc chương trình',
                        'transport'     => 'Xe du lịch + Máy bay khứ hồi',
                    ],
                ],
                'inclusions'              => [
                    'Vé máy bay khứ hồi SGN - SIN // KUL - SGN gồm thuế phí sân bay.',
                    '3 đêm khách sạn 3-4 sao chuẩn quốc tế (2 khách/phòng).',
                    'Xe đưa đón xuyên quốc gia máy lạnh tiện nghi đời mới.',
                    'Các bữa ăn theo lịch trình với món ăn đặc trưng Trung Hoa, Malaysia, Nyonya.',
                    'Vé cáp treo cao nguyên Genting và các điểm tham quan.',
                    'Bảo hiểm du lịch quốc tế quyền lợi 220.000.000 VND.',
                ],
                'exclusions'              => [
                    'Tiền Tips phục vụ HDV và tài xế: 5 USD/khách/ngày (20 USD cả hành trình).',
                    'Phòng đơn phụ thu 3.100.000 VND, thuế VAT.',
                ],
                'cancellation_terms'      => [
                    ['timeframe' => 'Sau khi đăng ký cọc tour', 'penalty_rate' => '50% tiền cọc'],
                    ['timeframe' => 'Trước 15 ngày khởi hành', 'penalty_rate' => '75% tổng giá tour'],
                    ['timeframe' => 'Dưới 10 ngày khởi hành', 'penalty_rate' => '100% tổng giá tour'],
                ],
            ],

            // Tour 8: Nhật Bản 5N5Đ Tokyo - Phú Sĩ - Kyoto - Osaka
            [
                'id'                      => 108,
                'title'                   => 'Tour Nhật Bản 5N5Đ: Tokyo - Núi Phú Sĩ - Tàu Shinkansen - Cố Đô Kyoto - Osaka',
                'tour_code'               => 'NB5NTN-2409',
                'slug'                    => 'tour-nhat-ban-tokyo-phu-si-kyoto-osaka-NB5NTN-2409',
                'market_type'             => 2,
                'duration_days'           => 5,
                'duration_nights'         => 5,
                'duration_label'          => '5N5Đ (5 Ngày 5 Đêm)',
                'departure_city'          => 'TP. Hồ Chí Minh / Hà Nội',
                'destination_primary'     => 'Tokyo - Phú Sĩ - Kyoto - Osaka',
                'destination_country'     => 'Nhật Bản',
                'destination_route'       => 'TP.HCM - Narita Tokyo - Núi Phú Sĩ - Trải nghiệm Shinkansen - Kyoto - Osaka - TP.HCM',
                'transportation'          => 'Hàng không Quốc tế 4 sao (ANA / Vietnam Airlines), Tàu cao tốc Shinkansen, Xe du lịch Nhật Bản',
                'hotel_stars'             => '3* - 4* kèm 01 đêm Onsen',
                'adult_price'             => 34890000,
                'child_price'             => 30500000,
                'infant_price'            => 7000000,
                'single_room_supplement'  => 7500000,
                'tour_status'             => 'available',
                'tour_type_slug'          => 'quoc-te',
                'destination_slug'        => 'nhat-ban',
                'departures'              => [
                    [
                        'departure_id'    => 4501,
                        'departure_date'  => '2026-10-20',
                        'return_date'     => '2026-10-25',
                        'day_of_week'     => 'Thứ 3',
                        'duration_days'   => 5,
                        'max_seats'       => 20,
                        'remaining_seats' => 4,
                        'price'           => 34890000,
                        'status'          => 'limited',
                    ],
                    [
                        'departure_id'    => 4502,
                        'departure_date'  => '2026-11-10',
                        'return_date'     => '2026-11-15',
                        'day_of_week'     => 'Thứ 3',
                        'duration_days'   => 5,
                        'max_seats'       => 20,
                        'remaining_seats' => 9,
                        'price'           => 34890000,
                        'status'          => 'available',
                    ],
                    [
                        'departure_id'    => 4503,
                        'departure_date'  => '2026-11-24',
                        'return_date'     => '2026-11-29',
                        'day_of_week'     => 'Thứ 3',
                        'duration_days'   => 5,
                        'max_seats'       => 20,
                        'remaining_seats' => 14,
                        'price'           => 34890000,
                        'status'          => 'available',
                    ],
                ],
                'highlights'              => [
                    'Chinh phục biểu tượng thiêng liêng Núi Phú Sĩ - Di sản thế giới UNESCO.',
                    'Tặng trải nghiệm tắm suối khoáng nóng khoáng chất tự nhiên Onsen chuẩn truyền thống Nhật.',
                    'Trải nghiệm tốc độ tàu viên đạn siêu tốc Shinkansen niềm tự hào công nghệ xứ Phù Tang.',
                    'Khám phá Cố Đô Kyoto cổ kính với Chùa Vàng Kinkakuji và Đền nghìn cổng Fushimi Inari.',
                ],
                'daily_itinerary'         => [
                    [
                        'day'           => 1,
                        'title'         => 'ĐÊM 1 & NGÀY 1: TP.HCM – NARITA TOKYO – HOÀNG CUNG – GINZA (Ăn trưa, tối)',
                        'meals'         => ['summary' => 'Ăn trưa bò Nhật, tối', 'breakfast' => false, 'lunch' => true, 'dinner' => true],
                        'desc'          => '23h30 đêm trước tập trung tại sân bay Tân Sơn Nhất đáp chuyến bay đi Tokyo. Sáng đến sân bay Narita, xe đón đoàn tham quan Hoàng Cung Tokyo (Imperial Palace). Trưa thưởng thức bò Nhật nướng Teppanyaki. Chiều chiêm bái Chùa cổ Asakusa Kannon lâu đời nhất Tokyo, ngắm Tháp Tokyo Skytree, mua sắm tại phố sầm uất Ginza.',
                        'accommodation' => 'Khách sạn 3-4 sao Tokyo (Villa Fontaine / APA Resort)',
                        'transport'     => 'Máy bay quốc tế + Xe du lịch Nhật',
                    ],
                    [
                        'day'           => 2,
                        'title'         => 'NGÀY 2: TOKYO – NÚI PHÚ SĨ – LÀNG CỔ OSHINO HAKKAI – TẮM ONSEN (Ăn 3 bữa)',
                        'meals'         => ['summary' => 'Ăn 3 bữa, tối Kaiseki', 'breakfast' => true, 'lunch' => true, 'dinner' => true],
                        'desc'          => 'Ăn sáng, xe đưa đoàn đến Núi Phú Sĩ, di chuyển lên Trạm số 5 (tùy điều kiện thời tiết). Chiêm ngưỡng Làng cổ Oshino Hakkai thanh bình dưới chân núi với 8 hồ nước trong vắt từ tuyết tan. Tối nhận phòng tại khu nghỉ dưỡng suối nước nóng, mặc trang phục Yukata thưởng thức đại tiệc Kaiseki và tắm khoáng nóng Onsen.',
                        'accommodation' => 'Khách sạn Onsen truyền thống khu vực Phú Sĩ',
                        'transport'     => 'Xe du lịch Nhật Bản',
                    ],
                    [
                        'day'           => 3,
                        'title'         => 'NGÀY 3: TRẢI NGHIỆM TÀU SHINKANSEN – CỐ ĐÔ KYOTO – CHÙA VÀNG (Ăn 3 bữa)',
                        'meals'         => ['summary' => 'Ăn 3 bữa', 'breakfast' => true, 'lunch' => true, 'dinner' => true],
                        'desc'          => 'Đoàn trải nghiệm tàu siêu tốc Shinkansen tốc độ 300km/h di chuyển đến Cố Đô Kyoto. Tham quan Chùa Vàng Kinkakuji dát vàng lấp lánh soi bóng trên mặt hồ gương. Dạo bước trong Rừng Trúc xanh ngắt Arashiyama thơ mộng. Chiều viếng Đền thờ Thần đạo Fushimi Inari Taisha với hơn 10.000 cổng Torii màu đỏ rực rỡ.',
                        'accommodation' => 'Khách sạn 4 sao Kyoto / Osaka',
                        'transport'     => 'Tàu cao tốc Shinkansen + Xe du lịch',
                    ],
                    [
                        'day'           => 4,
                        'title'         => 'NGÀY 4: CỐ ĐÔ KYOTO – OSAKA – LÂU ĐÀI OSAKA – SHINSAIBASHI (Ăn sáng, trưa)',
                        'meals'         => ['summary' => 'Ăn sáng, trưa (tối tự túc)', 'breakfast' => true, 'lunch' => true, 'dinner' => false],
                        'desc'          => 'Di chuyển về thành phố cảng năng động Osaka. Chụp ảnh lưu niệm bên ngoài Lâu đài Osaka uy nghiêm kiệt tác của Toyotomi Hideyoshi. Thưởng thức bữa trưa sushi và mì Udon truyền thống. Chiều tự do mua sắm và thưởng thức ẩm thực đường phố takoyaki tại thiên đường mua sắm sầm uất Shinsaibashi và Dotonbori.',
                        'accommodation' => 'Khách sạn 4 sao trung tâm Osaka',
                        'transport'     => 'Xe du lịch đời mới',
                    ],
                    [
                        'day'           => 5,
                        'title'         => 'NGÀY 5: SÂN BAY KANSAI OSAKA – TP. HỒ CHÍ MINH (Ăn sáng)',
                        'meals'         => ['summary' => 'Ăn sáng buffet', 'breakfast' => true, 'lunch' => false, 'dinner' => false],
                        'desc'          => 'Đoàn dùng buffet sáng, làm thủ tục trả phòng. Xe đưa đoàn ra Sân bay quốc tế Kansai (KIX) làm thủ tục đáp chuyến bay thẳng về lại sân bay Tân Sơn Nhất TP.HCM. Trưởng đoàn hỗ trợ thủ tục nhập cảnh, kết thúc chuyến du xuân xứ sở mặt trời mọc.',
                        'accommodation' => 'Kết thúc chương trình',
                        'transport'     => 'Xe du lịch + Máy bay khứ hồi',
                    ],
                ],
                'inclusions'              => [
                    'Vé máy bay khứ hồi SGN - NRT // KIX - SGN gồm 46kg ký gửi (2 kiện) và 10kg xách tay.',
                    'Visa du lịch nhập cảnh Nhật Bản diện đoàn du lịch trọn gói.',
                    'Lưu trú 4 sao (kèm 01 đêm khách sạn Onsen trải nghiệm tắm suối khoáng nóng).',
                    'Vé tàu cao tốc Shinkansen chặng Hamamatsu - Toyohashi hoặc tương đương.',
                    'Toàn bộ vé tham quan danh lam thắng cảnh và bữa ăn theo chương trình.',
                    'Bảo hiểm du lịch quốc tế bồi thường đến 1.050.000.000 VND (50.000 USD).',
                ],
                'exclusions'              => [
                    'Tiền Tips cho HDV và tài xế Nhật: 8 USD/khách/ngày (40 USD/tour).',
                    'Phụ thu phòng đơn 7.500.000 VND, thuế VAT, chi tiêu cá nhân.',
                ],
                'cancellation_terms'      => [
                    ['timeframe' => 'Sau khi nộp hồ sơ xin visa', 'penalty_rate' => '100% lệ phí visa + cọc vé'],
                    ['timeframe' => 'Trước 30 ngày khởi hành', 'penalty_rate' => '50% giá tour'],
                    ['timeframe' => 'Trước 15 ngày khởi hành', 'penalty_rate' => '80% giá tour'],
                    ['timeframe' => 'Dưới 10 ngày khởi hành', 'penalty_rate' => '100% giá tour'],
                ],
            ],

            // Tour 9: Hàn Quốc 5N4D Seoul - Nami - Everland
            [
                'id'                      => 109,
                'title'                   => 'Tour Hàn Quốc 5N4D: Seoul - Đảo Nami - Công Viên Everland - Cung Điện Gyeongbok',
                'tour_code'               => 'HQ5NSE-2026',
                'slug'                    => 'tour-han-quoc-seoul-nami-everland-HQ5NSE-2026',
                'market_type'             => 2,
                'duration_days'           => 5,
                'duration_nights'         => 4,
                'duration_label'          => '5N4D (5 Ngày 4 Đêm)',
                'departure_city'          => 'TP. Hồ Chí Minh / Hà Nội',
                'destination_primary'     => 'Seoul',
                'destination_country'     => 'Hàn Quốc',
                'destination_route'       => 'TP.HCM - Incheon - Đảo Nami - Seoul - Everland - Cung Gyeongbok - TP.HCM',
                'transportation'          => 'Vé máy bay thẳng khứ hồi, Xe du lịch đời mới sưởi ấm 45 chỗ',
                'hotel_stars'             => '4* tiêu chuẩn Hàn Quốc',
                'adult_price'             => 14990000,
                'child_price'             => 12990000,
                'infant_price'            => 3500000,
                'single_room_supplement'  => 3800000,
                'tour_status'             => 'available',
                'tour_type_slug'          => 'quoc-te',
                'destination_slug'        => 'han-quoc',
                'departures'              => [
                    [
                        'departure_id'    => 4601,
                        'departure_date'  => '2026-10-18',
                        'return_date'     => '2026-10-22',
                        'day_of_week'     => 'Chủ nhật',
                        'duration_days'   => 5,
                        'max_seats'       => 25,
                        'remaining_seats' => 11,
                        'price'           => 14990000,
                        'status'          => 'available',
                    ],
                    [
                        'departure_id'    => 4602,
                        'departure_date'  => '2026-11-08',
                        'return_date'     => '2026-11-12',
                        'day_of_week'     => 'Chủ nhật',
                        'duration_days'   => 5,
                        'max_seats'       => 25,
                        'remaining_seats' => 6,
                        'price'           => 14990000,
                        'status'          => 'limited',
                    ],
                    [
                        'departure_id'    => 4603,
                        'departure_date'  => '2026-11-22',
                        'return_date'     => '2026-11-26',
                        'day_of_week'     => 'Chủ nhật',
                        'duration_days'   => 5,
                        'max_seats'       => 25,
                        'remaining_seats' => 18,
                        'price'           => 14990000,
                        'status'          => 'available',
                    ],
                ],
                'highlights'              => [
                    'Khám phá Đảo Nami xinh đẹp - phim trường tác phẩm kinh điển Bản Tình Ca Mùa Đông.',
                    'Vui chơi không giới hạn tại Công viên giải trí Everland thuộc top 10 công viên lớn nhất thế giới.',
                    'Check-in Cung điện Cảnh Phúc Gyeongbokgung, khoác lên mình trang phục Hanbok truyền thống.',
                    'Thưởng thức ẩm thực chuẩn Hàn: Gà hầm sâm Samgyetang, thịt nướng Bulgogi, lẩu kim chi.',
                ],
                'daily_itinerary'         => [
                    [
                        'day'           => 1,
                        'title'         => 'ĐÊM 1 & NGÀY 1: TP.HCM – INCHEON SEOUL – ĐẢO NAMI (Ăn sáng, trưa, tối)',
                        'meals'         => ['summary' => 'Ăn sáng canh sườn bò, trưa gà nướng, tối', 'breakfast' => true, 'lunch' => true, 'dinner' => true],
                        'desc'          => 'Bay đêm từ sân bay Tân Sơn Nhất đi Seoul. Sáng hạ cánh sân bay Incheon, dùng bữa sáng canh sườn bò giải rượu Galbitang. Khởi hành đi Đảo Nami - hòn đảo hình bán nguyệt thơ mộng với những rặng ngân hạnh vàng rực. Thưởng thức gà nướng chảo thơm lừng. Chiều về Seoul nhận phòng khách sạn 4 sao nghỉ ngơi.',
                        'accommodation' => 'Khách sạn 4 sao Seoul (Bernoui / Golden City)',
                        'transport'     => 'Máy bay quốc tế + Xe du lịch sưởi ấm',
                    ],
                    [
                        'day'           => 2,
                        'title'         => 'NGÀY 2: CÔNG VIÊN GIẢI TRÍ EVERLAND – TRẢI NGHIỆM MẶC HANBOK (Ăn 3 bữa)',
                        'meals'         => ['summary' => 'Ăn 3 bữa', 'breakfast' => true, 'lunch' => true, 'dinner' => true],
                        'desc'          => 'Khám phá Công viên giải trí Everland với tàu lượn siêu tốc bằng gỗ T-Express ngoạn mục, vườn thú Safari hoang dã. Trưa thưởng thức thịt nướng than hoa Hàn Quốc. Chiều tham gia lớp học làm kim chi truyền thống và chụp ảnh lưu niệm trong trang phục Hanbok hoàng gia.',
                        'accommodation' => 'Khách sạn 4 sao Seoul',
                        'transport'     => 'Xe du lịch 45 chỗ',
                    ],
                    [
                        'day'           => 3,
                        'title'         => 'NGÀY 3: CUNG ĐIỆN GYEONGBOKGUNG – NHÀ XANH – THÁP NAMSAN (Ăn 3 bữa)',
                        'meals'         => ['summary' => 'Ăn 3 bữa gà hầm sâm', 'breakfast' => true, 'lunch' => true, 'dinner' => true],
                        'desc'          => 'Tham quan Cung điện Hoàng Gia Gyeongbokgung cổ kính 600 năm tuổi, xem nghi thức đổi gác của thị vệ triều Joseon. Chụp ảnh bên ngoài Nhà Xanh Cheongwadae (Phủ Tổng Thống). Trưa dùng gà hầm sâm thảo mộc bồi bổ sức khỏe. Chiều lên Tháp truyền hình Namsan chiêm ngưỡng hàng triệu ổ khóa tình yêu.',
                        'accommodation' => 'Khách sạn 4 sao Seoul',
                        'transport'     => 'Xe du lịch máy lạnh',
                    ],
                    [
                        'day'           => 4,
                        'title'         => 'NGÀY 4: TRUNG TÂM NHÂN SÂM CHÍNH PHỦ – MUA SẮM MYEONGDONG (Ăn sáng, trưa)',
                        'meals'         => ['summary' => 'Ăn sáng, trưa (tối tự túc)', 'breakfast' => true, 'lunch' => true, 'dinner' => false],
                        'desc'          => 'Tìm hiểu và mua sắm tại Trung tâm nhân sâm Hoàng Gia, Tinh dầu thông đỏ bảo bối sức khỏe Hàn Quốc. Dùng bữa trưa lẩu nấm kim chi. Chiều tự do mua sắm mỹ phẩm nội địa và thưởng thức ẩm thực vặt nức tiếng tại thiên đường mua sắm Myeongdong náo nhiệt.',
                        'accommodation' => 'Khách sạn 4 sao Seoul',
                        'transport'     => 'Xe du lịch máy lạnh',
                    ],
                    [
                        'day'           => 5,
                        'title'         => 'NGÀY 5: LÀNG BÍCH HỌA IHWA – INCHEON – TP. HỒ CHÍ MINH (Ăn sáng, trưa)',
                        'meals'         => ['summary' => 'Ăn sáng, trưa', 'breakfast' => true, 'lunch' => true, 'dinner' => false],
                        'desc'          => 'Tham quan Làng tranh bích họa Ihwa yên bình với những bậc thang nghệ thuật tuyệt đẹp. Ghé siêu thị Ponglim đóng gói hành lý và mua đặc sản bánh kẹo Hàn Quốc. Sau bữa trưa, xe đưa đoàn ra sân bay quốc tế Incheon đáp chuyến bay về TP.HCM.',
                        'accommodation' => 'Kết thúc chương trình',
                        'transport'     => 'Xe du lịch + Máy bay khứ hồi',
                    ],
                ],
                'inclusions'              => [
                    'Vé máy bay khứ hồi thẳng TP.HCM - Incheon - TP.HCM gồm 20kg ký gửi.',
                    'Lệ phí visa nhập cảnh Hàn Quốc.',
                    '4 đêm khách sạn 4 sao tiêu chuẩn Hàn Quốc (2 khách/phòng).',
                    'Vé vui chơi trọn gói công viên Everland và vé tham quan đảo Nami.',
                    'Các bữa ăn chuẩn hương vị xứ sở kim chi gồm gà hầm sâm, thịt nướng Bulgogi.',
                    'Bảo hiểm du lịch quốc tế bồi thường tối đa 220.000.000 VND.',
                ],
                'exclusions'              => [
                    'Tiền Tip quy định bắt buộc cho HDV và tài xế: 7 USD/khách/ngày (35 USD/tour).',
                    'Phòng đơn phụ thu 3.800.000 VND, thuế VAT.',
                ],
                'cancellation_terms'      => [
                    ['timeframe' => 'Sau khi đặt cọc', 'penalty_rate' => '50% tiền cọc'],
                    ['timeframe' => 'Trước 20 ngày', 'penalty_rate' => '50% tổng tour'],
                    ['timeframe' => 'Dưới 10 ngày', 'penalty_rate' => '100% tổng tour'],
                ],
            ],

            // Tour 10: Châu Âu 4 Nước 10N9Đ Pháp - Thụy Sỹ - Ý - Vatican
            [
                'id'                      => 110,
                'title'                   => 'Tour Châu Âu 4 Nước 10N9Đ: Pháp - Thụy Sỹ - Ý - Vatican Hành Trình Di Sản',
                'tour_code'               => 'EU10NVVT-0210',
                'slug'                    => 'tour-chau-au-4-nuoc-phap-thuy-sy-y-vatican-EU10NVVT-0210',
                'market_type'             => 2,
                'duration_days'           => 10,
                'duration_nights'         => 9,
                'duration_label'          => '10N9Đ (10 Ngày 9 Đêm)',
                'departure_city'          => 'TP. Hồ Chí Minh / Hà Nội',
                'destination_primary'     => 'Pháp - Thụy Sỹ - Ý - Vatican',
                'destination_country'     => 'Pháp / Thụy Sỹ / Ý / Vatican',
                'destination_route'       => 'Paris - Colmar - Lucerne - Đỉnh Titlis - Milan - Venice - Pisa - Florence - Rome - Vatican',
                'transportation'          => 'Hàng không 5 sao (Qatar Airways / Emirates / Air France), Xe bus đường dài tiêu chuẩn Châu Âu, Du thuyền Venice',
                'hotel_stars'             => '4* tiêu chuẩn Châu Âu',
                'adult_price'             => 79900000,
                'child_price'             => 69900000,
                'infant_price'            => 18000000,
                'single_room_supplement'  => 16500000,
                'tour_status'             => 'available',
                'tour_type_slug'          => 'quoc-te',
                'destination_slug'        => 'phap-thuy-sy-y',
                'departures'              => [
                    [
                        'departure_id'    => 4701,
                        'departure_date'  => '2026-10-25',
                        'return_date'     => '2026-11-03',
                        'day_of_week'     => 'Chủ nhật',
                        'duration_days'   => 10,
                        'max_seats'       => 20,
                        'remaining_seats' => 5,
                        'price'           => 79900000,
                        'status'          => 'limited',
                    ],
                    [
                        'departure_id'    => 4702,
                        'departure_date'  => '2026-11-15',
                        'return_date'     => '2026-11-24',
                        'day_of_week'     => 'Chủ nhật',
                        'duration_days'   => 10,
                        'max_seats'       => 20,
                        'remaining_seats' => 8,
                        'price'           => 79900000,
                        'status'          => 'available',
                    ],
                    [
                        'departure_id'    => 4703,
                        'departure_date'  => '2026-12-05',
                        'return_date'     => '2026-12-14',
                        'day_of_week'     => 'Thứ 7',
                        'duration_days'   => 10,
                        'max_seats'       => 20,
                        'remaining_seats' => 12,
                        'price'           => 79900000,
                        'status'          => 'available',
                    ],
                ],
                'highlights'              => [
                    'Chiêm ngưỡng tháp Eiffel biểu tượng nước Pháp và du thuyền lãng mạn trên dòng sông Seine.',
                    'Chinh phục đỉnh núi tuyết vĩnh cửu Titlis Thụy Sỹ ở độ cao 3.020m bằng cáp treo xoay 360 độ.',
                    'Lạc bước vào Venice - thành phố tình yêu trên sông không tiếng còi xe độc nhất vô nhị.',
                    'Khám phá Đấu trường La Mã Colosseum và Vương cung thánh đường St. Peter tại Tòa Thánh Vatican.',
                ],
                'daily_itinerary'         => [
                    [
                        'day'           => 1,
                        'title'         => 'NGÀY 1: TP.HCM – DOHA / DUBAI – THỦ ĐÔ PARIS (Ăn trên máy bay)',
                        'meals'         => ['summary' => 'Ăn uống tiêu chuẩn trên máy bay 5 sao', 'breakfast' => false, 'lunch' => false, 'dinner' => false],
                        'desc'          => 'Quý khách có mặt tại sân bay Tân Sơn Nhất đáp chuyến bay của hãng hàng không 5 sao nối chuyến đi Paris hoa lệ. Nghỉ đêm trên máy bay.',
                        'accommodation' => 'Nghỉ đêm trên máy bay 5 sao',
                        'transport'     => 'Máy bay quốc tế',
                    ],
                    [
                        'day'           => 2,
                        'title'         => 'NGÀY 2: PARIS – THÁP EIFFEL – DU THUYỀN SÔNG SEINE – BẢO TÀNG LOUVRE (Ăn trưa, tối)',
                        'meals'         => ['summary' => 'Ăn trưa, tối ẩm thực Pháp', 'breakfast' => true, 'lunch' => true, 'dinner' => true],
                        'desc'          => 'Hạ cánh sân bay Charles de Gaulle Paris. Tham quan Khải Hoàn Môn Arc de Triomphe, Đại lộ Champs-Élysées. Chụp ảnh lưu niệm tại Tháp Eiffel biểu tượng nước Pháp. Du thuyền Bateaux Parisiens trên sông Seine thơ mộng. Chiều chụp ảnh bên ngoài Bảo tàng Louvre với kim tự tháp kính nổi tiếng.',
                        'accommodation' => 'Khách sạn 4 sao Paris (Mercure / Novotel)',
                        'transport'     => 'Du thuyền sông Seine + Xe bus Châu Âu',
                    ],
                    [
                        'day'           => 3,
                        'title'         => 'NGÀY 3: PARIS – REIMS THỦ PHỦ RƯỢU VANG – LÀNG CỔ COLMAR (Ăn 3 bữa)',
                        'meals'         => ['summary' => 'Ăn 3 bữa', 'breakfast' => true, 'lunch' => true, 'dinner' => true],
                        'desc'          => 'Khởi hành đi Reims - thủ phủ vùng rượu sâm panh danh tiếng, viếng Nhà thờ Đức Bà Reims nơi đăng quang của 25 vị vua Pháp. Chiều di chuyển về Colmar - ngôi làng cổ tích đẹp nhất nước Pháp với những ngôi nhà nửa gỗ rực rỡ bên dòng kênh xanh Petite Venise.',
                        'accommodation' => 'Khách sạn 4 sao Colmar / Mulhouse',
                        'transport'     => 'Xe bus đường dài Châu Âu',
                    ],
                    [
                        'day'           => 4,
                        'title'         => 'NGÀY 4: COLMAR – THỤY SỸ – THÀNH PHỐ LUCERNE – CẦU GỖ CHAPEL (Ăn 3 bữa)',
                        'meals'         => ['summary' => 'Ăn 3 bữa lẩu phô mai Fondue', 'breakfast' => true, 'lunch' => true, 'dinner' => true],
                        'desc'          => 'Nhập cảnh Thụy Sỹ xinh đẹp, tiến về thành phố Lucerne cổ kính nép mình bên hồ nước trong xanh. Dạo bước trên Cầu gỗ Chapel thế kỷ 14 cổ nhất Châu Âu, chiêm ngưỡng Tượng đài Sư tử đá hấp hối tưởng niệm lính Thụy Sỹ. Thưởng thức đặc sản lẩu phô mai Fondue Thụy Sỹ.',
                        'accommodation' => 'Khách sạn 4 sao Lucerne / Engelberg',
                        'transport'     => 'Xe du lịch Châu Âu',
                    ],
                    [
                        'day'           => 5,
                        'title'         => 'NGÀY 5: NÚI TUYẾT TITLIS 3.020M – KINH ĐÔ THỜI TRANG MILAN Ý (Ăn 3 bữa)',
                        'meals'         => ['summary' => 'Ăn 3 bữa', 'breakfast' => true, 'lunch' => true, 'dinner' => true],
                        'desc'          => 'Chinh phục Đỉnh núi tuyết Titlis quanh năm tuyết trắng bằng cáp treo xoay 360 độ Rotair độc đáo. Đi bộ qua cầu treo cao nhất Châu Âu Titlis Cliff Walk. Trưa ăn cơm tại nhà hàng trên đỉnh núi. Chiều xuôi về nước Ý xinh đẹp, đến kinh đô thời trang Milan chiêm ngưỡng Thánh đường Duomo tráng lệ.',
                        'accommodation' => 'Khách sạn 4 sao Milan (Cosmo Palace / NH)',
                        'transport'     => 'Cáp treo Titlis + Xe du lịch Ý',
                    ],
                    [
                        'day'           => 6,
                        'title'         => 'NGÀY 6: MILAN – THÀNH PHỐ TRÊN SÔNG VENICE – THÁP NGHIÊNG PISA (Ăn 3 bữa)',
                        'meals'         => ['summary' => 'Ăn 3 bữa mỳ Ý pizza', 'breakfast' => true, 'lunch' => true, 'dinner' => true],
                        'desc'          => 'Lên thuyền vượt vịnh vào đảo Venice lãng mạn. Tham quan Quảng trường Thánh Marco, Dinh Tổng trấn Doge’s Palace, Cầu Than Thở Ponte dei Sospiri. Chiều di chuyển về vùng Tuscany đến thành phố Pisa chiêm ngưỡng Tháp nghiêng Pisa kỳ quan kiến trúc thế giới.',
                        'accommodation' => 'Khách sạn 4 sao Pisa / Florence',
                        'transport'     => 'Thuyền Venice + Xe bus Châu Âu',
                    ],
                    [
                        'day'           => 7,
                        'title'         => 'NGÀY 7: PISA – CỐ ĐÔ NGHỆ THUẬT FLORENCE – THỦ ĐÔ ROME (Ăn 3 bữa)',
                        'meals'         => ['summary' => 'Ăn 3 bữa bò bít tết Bistecca', 'breakfast' => true, 'lunch' => true, 'dinner' => true],
                        'desc'          => 'Tham quan Cố đô nghệ thuật phục hưng Florence (Firenze): Nhà thờ chính tòa Santa Maria del Fiore lộng lẫy, Cầu cổ Ponte Vecchio bắc qua sông Arno. Trưa thưởng thức bít tết Florence Bistecca alla Fiorentina. Chiều khởi hành về thủ đô Rome cổ kính.',
                        'accommodation' => 'Khách sạn 4 sao trung tâm Rome (Ergife / Holiday Inn)',
                        'transport'     => 'Xe du lịch Châu Âu',
                    ],
                    [
                        'day'           => 8,
                        'title'         => 'NGÀY 8: ĐẤU TRƯỜNG LA MÃ COLOSSEUM – ĐÀI PHUN NƯỚC TREVI (Ăn 3 bữa)',
                        'meals'         => ['summary' => 'Ăn 3 bữa', 'breakfast' => true, 'lunch' => true, 'dinner' => true],
                        'desc'          => 'Tham quan Đấu trường La Mã Colosseum - biểu tượng hùng mạnh của Đế chế La Mã hơn 2.000 năm lịch sử. Khám phá Di chỉ Khảo cổ Roman Forum, Đền Pantheon kiệt tác mái vòm cổ đại. Thả đồng xu cầu nguyện may mắn tại Đài phun nước Trevi trứ danh và thưởng thức kem Gelato mát lạnh.',
                        'accommodation' => 'Khách sạn 4 sao Rome',
                        'transport'     => 'Xe du lịch Rome',
                    ],
                    [
                        'day'           => 9,
                        'title'         => 'NGÀY 9: CÔNG QUỐC VATICAN – VƯƠNG CUNG THÁNH ĐƯỜNG ST. PETER – SÂN BAY (Ăn sáng, trưa)',
                        'meals'         => ['summary' => 'Ăn sáng, trưa', 'breakfast' => true, 'lunch' => true, 'dinner' => false],
                        'desc'          => 'Viếng thăm Quốc gia nhỏ nhất thế giới Vatican - trung tâm Giáo hội Công giáo toàn cầu. Chiêm bái Vương cung Thánh đường St. Peter tráng lệ kiệt tác của Michelangelo. Dùng bữa trưa trước khi xe đưa đoàn ra sân bay quốc tế Fiumicino Rome đáp chuyến bay về Việt Nam.',
                        'accommodation' => 'Nghỉ đêm trên máy bay',
                        'transport'     => 'Xe du lịch + Máy bay quốc tế 5 sao',
                    ],
                    [
                        'day'           => 10,
                        'title'         => 'NGÀY 10: VỀ ĐẾN TP. HỒ CHÍ MINH (Ăn trên máy bay)',
                        'meals'         => ['summary' => 'Ăn uống trên chuyến bay', 'breakfast' => false, 'lunch' => false, 'dinner' => false],
                        'desc'          => 'Chuyến bay hạ cánh tại sân bay Tân Sơn Nhất TP.HCM. Trưởng đoàn hỗ trợ thủ tục nhập cảnh và nhận hành lý. Kết thúc chuyến hành trình khám phá Tây Âu 4 nước đầy ấn tượng.',
                        'accommodation' => 'Kết thúc chương trình',
                        'transport'     => 'Máy bay quốc tế',
                    ],
                ],
                'inclusions'              => [
                    'Vé máy bay quốc tế 5 sao khứ hồi (SGN - CDG // FCO - SGN) gồm thuế phí hàng không.',
                    'Lệ phí xin visa Schengen nhập cảnh Châu Âu.',
                    'Toàn bộ 8 đêm khách sạn 4 sao tiêu chuẩn Châu Âu cao cấp (2 khách/phòng).',
                    'Vé cáp treo xoay 360 độ lên đỉnh núi tuyết Titlis Thụy Sỹ.',
                    'Vé du thuyền sông Seine Paris và vé tàu sang đảo Venice.',
                    'Bảo hiểm du lịch quốc tế hạn mức tối đa 1.500.000.000 VND (50.000 EUR).',
                    'Các bữa ăn cao cấp xuyên suốt hành trình kết hợp ẩm thực Âu và Á.',
                ],
                'exclusions'              => [
                    'Tiền Tip phục vụ tài xế và HDV Châu Âu: 10 EUR/khách/ngày (90 EUR/tour).',
                    'Phòng đơn phụ thu 16.500.000 VND, thuế VAT.',
                ],
                'cancellation_terms'      => [
                    ['timeframe' => 'Sau khi nộp hồ sơ xin visa', 'penalty_rate' => '100% lệ phí visa + phí giữ chỗ'],
                    ['timeframe' => 'Trước 35 ngày khởi hành', 'penalty_rate' => '50% tổng giá tour'],
                    ['timeframe' => 'Trước 20 ngày khởi hành', 'penalty_rate' => '80% tổng giá tour'],
                    ['timeframe' => 'Dưới 15 ngày khởi hành', 'penalty_rate' => '100% tổng giá tour'],
                ],
            ],
        ];
    }

    /**
     * Build the 4 sample Cẩm nang du lịch posts.
     *
     * WHY: Provides rich editorial content for the travel guide category and internal SEO linking.
     *
     * @return array
     */
    private function build_guide_posts(): array
    {
        return [
            [
                'id'         => 201,
                'title'      => 'Tips du lịch Nha Trang tự túc từ A-Z tiết kiệm chi phí cho gia đình',
                'slug'       => 'tips-du-lich-nha-trang-tu-tuc-tiet-kiem',
                'excerpt'    => 'Tổng hợp kinh nghiệm đặt phòng khách sạn, lựa chọn phương tiện di chuyển, lịch trình tắm biển Dốc Lết và thưởng thức hải sản ngon rẻ tại Nha Trang.',
                'category'   => 'cam-nang-du-lich',
                'post_date'  => '2026-09-10 08:30:00',
                'content'    => <<<HTML
<!-- wp:paragraph -->
<p>Nha Trang luôn là điểm đến hàng đầu của du khách trong và ngoài nước nhờ đường bờ biển cong vút, cát trắng thoai thoải và khí hậu ôn hòa quanh năm. Để chuyến du lịch gia đình vừa thoải mái vừa tối ưu chi phí, việc chuẩn bị lịch trình và nắm bắt mẹo vặt địa phương là vô cùng quan trọng.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>1. Thời điểm lý tưởng nhất để đến vịnh biển Nha Trang</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Khí hậu Nha Trang chia thành hai mùa rõ rệt. Mùa khô kéo dài từ tháng 1 đến tháng 8, thời tiết nắng ráo, nước biển êm ả và tầm nhìn dưới nước rất tốt cho các hoạt động lặn ngắm san hô. Từ tháng 9 đến tháng 12, phố biển bắt đầu đón những cơn mưa rào, tuy nhiên giá vé máy bay và phòng khách sạn giai đoạn này lại cực kỳ ưu đãi.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>2. Phương tiện di chuyển tiết kiệm ngân sách</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Nếu xuất phát từ TP.HCM, bạn có thể lựa chọn tàu hỏa đêm chất lượng cao (5 sao) hoặc xe khách giường nằm cao cấp để tiết kiệm một đêm khách sạn. Nếu bay từ Hà Nội, hãy lên kế hoạch săn vé trước ít nhất 3 đến 4 tuần từ các hãng hàng không nội địa.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>3. Ẩm thực nhất định phải thử</h2>
<!-- /wp:heading -->
<!-- wp:list -->
<ul>
    <li><strong>Nem nướng Ninh Hòa:</strong> Ăn kèm bánh tráng chiên giòn, rau sống và nước chấm đậu phộng sền sệt đậm đà.</li>
    <li><strong>Bún sứa, bún chả cá:</strong> Nước dùng ngọt thanh nấu từ cá cờ, cá thu, lát chả cá dai giòn cùng sứa tươi sần sật.</li>
    <li><strong>Hải sản bờ kè Tháp Bà:</strong> Khu vực tập trung nhiều quán ốc, tôm hùm bình dân với giá niêm yết minh bạch.</li>
</ul>
<!-- /wp:list -->
HTML
            ],
            [
                'id'         => 202,
                'title'      => 'Kinh nghiệm đi Thái Lan lần đầu: Ăn gì, chơi đâu, đổi tiền thế nào?',
                'slug'       => 'kinh-nghiem-di-thai-lan-lan-dau-chi-tiet',
                'excerpt'    => 'Cẩm nang toàn diện cho người đi Thái Lan lần đầu: Thủ tục xuất nhập cảnh, sim du lịch 4G, kinh nghiệm mua sắm hoàn thuế VAT và ứng xử văn hóa tại xứ sở Chùa Vàng.',
                'category'   => 'cam-nang-du-lich',
                'post_date'  => '2026-09-12 10:15:00',
                'content'    => <<<HTML
<!-- wp:paragraph -->
<p>Thái Lan là quốc gia du lịch thân thiện hàng đầu Đông Nam Á đối với du khách Việt Nam. Với chính sách miễn thị thực 30 ngày cho hộ chiếu phổ thông, bạn chỉ cần hộ chiếu còn hạn trên 6 tháng là có thể tự tin xách ba lô lên đường khám phá Bangkok náo nhiệt và Pattaya sôi động.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>1. Hướng dẫn đổi tiền Baht Thái (THB) tỷ giá tốt</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Không nên đổi tiền tại sân bay quốc tế Suvarnabhumi hoặc Don Mueang vì tỷ giá chênh lệch khá lớn. Hãy chuẩn bị sẵn tiền mặt VND hoặc USD đổi tại các quầy SuperRich (màu xanh lá hoặc màu cam) tại trung tâm Bangkok như trạm BTS Chit Lom hoặc tầng hầm Airport Rail Link.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>2. Những quy tắc ứng xử văn hóa cần ghi nhớ</h2>
<!-- /wp:heading -->
<!-- wp:list -->
<ul>
    <li><strong>Trang phục đền chùa:</strong> Mặc quần hoặc váy dài quá đầu gối, áo có tay kín cổ khi viếng Hoàng Cung hoặc Chùa Phật Ngọc.</li>
    <li><strong>Tôn kính Hoàng gia:</strong> Tuyệt đối không có hành vi hoặc lời nói khiếm nhã về Hoàng gia và Quốc vương Thái Lan.</li>
    <li><strong>Không xoa đầu người khác:</strong> Trong quan niệm của người Thái, đầu là phần linh thiêng nhất trên cơ thể.</li>
</ul>
<!-- /wp:list -->
HTML
            ],
            [
                'id'         => 203,
                'title'      => 'Đi Phú Quốc mùa nào đẹp nhất trong năm? Kinh nghiệm săn combo vé máy bay và resort rẻ',
                'slug'       => 'di-phu-quoc-mua-nao-dep-nhat-trong-nam',
                'excerpt'    => 'Phân tích chu kỳ thời tiết biển Đảo Ngọc, thời điểm vàng tắm biển Bãi Sao, cách chọn khách sạn Bắc Đảo hay Nam Đảo phù hợp với gia đình có trẻ nhỏ.',
                'category'   => 'cam-nang-du-lich',
                'post_date'  => '2026-09-15 14:00:00',
                'content'    => <<<HTML
<!-- wp:paragraph -->
<p>Phú Quốc sở hữu hai mùa thời tiết tương phản rõ nét do ảnh hưởng của gió mùa Tây Nam và Đông Bắc. Hiểu rõ quy luật thời tiết giúp bạn chọn đúng bãi biển êm dịu, không sóng lớn và có được những bức ảnh check-in hoàng hôn rực rỡ nhất.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>1. Mùa khô (Tháng 11 đến tháng 4 năm sau) - Thời điểm vàng</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Đây là thời gian đẹp nhất của Đảo Ngọc với nhiệt độ trung bình khoảng 27-28 độ C, biển phẳng lặng như gương ở cả bờ Đông lẫn bờ Tây. Các hoạt động đi cano tham quan 4 đảo và câu cá lặn ngắm san hô diễn ra thuận lợi tuyệt đối.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>2. Nên ở Bắc Đảo hay Nam Đảo?</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Nếu gia đình có trẻ nhỏ yêu thích công viên chủ đề VinWonders, vườn thú bán hoang dã Safari và show thực cảnh Grand World, hãy ưu tiên lưu trú tại khu vực Bắc Đảo. Ngược lại, nếu bạn thích bãi tắm hoang sơ, ngắm hoàng hôn Sunset Town và đi cáp treo Hòn Thơm, các resort Nam Đảo và bãi Trường là sự lựa chọn hoàn hảo.</p>
<!-- /wp:paragraph -->
HTML
            ],
            [
                'id'         => 204,
                'title'      => 'Hướng dẫn chi tiết thủ tục xin visa du lịch Nhật Bản tự túc mới nhất',
                'slug'       => 'huong-dan-thu-tuc-xin-visa-du-lich-nhat-ban',
                'excerpt'    => 'Bộ hồ sơ chứng minh tài chính, sổ tiết kiệm, hợp đồng lao động và lịch trình chi tiết giúp nâng tỷ lệ đậu visa Nhật Bản lên mức tối đa.',
                'category'   => 'cam-nang-du-lich',
                'post_date'  => '2026-09-18 09:20:00',
                'content'    => <<<HTML
<!-- wp:paragraph -->
<p>Xin visa du lịch Nhật Bản tự túc hiện nay đã thông thoáng hơn rất nhiều nhờ việc áp dụng nộp hồ sơ qua các Trung tâm tiếp nhận thị thực ủy thác (VFS Global). Một bộ hồ sơ trung thực, logic và chứng minh mối ràng buộc chặt chẽ tại Việt Nam là chìa khóa để nhận kết quả visa suôn sẻ.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>1. Danh mục hồ sơ nhân thân và công việc bắt buộc</h2>
<!-- /wp:heading -->
<!-- wp:list -->
<ul>
    <li>Hộ chiếu gốc còn hạn trên 6 tháng và còn ít nhất 2 trang trống.</li>
    <li>Tờ khai xin cấp visa có dán ảnh 4.5cm x 3.5cm chụp trong vòng 6 tháng gần nhất.</li>
    <li>Hợp đồng lao động, quyết định bổ nhiệm và đơn xin nghỉ phép đi du lịch có xác nhận của công ty.</li>
    <li>Sao kê tài khoản trả lương 6 tháng gần nhất có mộc tròn ngân hàng.</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>2. Chứng minh tài chính và lịch trình du lịch</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Đại sứ quán Nhật Bản yêu cầu sổ tiết kiệm tối thiểu 100.000.000 VND kèm giấy xác nhận số dư tài khoản. Lịch trình chuyến đi phải chi tiết từng ngày bao gồm tên chuyến bay dự kiến, tên khách sạn lưu trú tương ứng với xác nhận đặt phòng (không bắt buộc thanh toán trước).</p>
<!-- /wp:paragraph -->
HTML
            ],
        ];
    }

    /**
     * Build the 4 sample service pages.
     *
     * WHY: Provides standardized landing pages for core travel auxiliary services
     * (Visa, Hotel Combo, Vehicle Rental, Teambuilding).
     *
     * @return array
     */
    private function build_service_pages(): array
    {
        return [
            [
                'id'        => 301,
                'title'     => 'Dịch Vụ Làm Visa Du Lịch & Công Tác Trọn Gói',
                'slug'      => 'dich-vu-lam-visa-tron-goi',
                'post_date' => '2026-09-01 09:00:00',
                'content'   => <<<HTML
<!-- wp:paragraph -->
<p>Chúng tôi cung cấp giải pháp tư vấn và xử lý hồ sơ thị thực (Visa) trọn gói chuyên nghiệp cho các quốc gia khắt khe: Nhật Bản, Hàn Quốc, Châu Âu (Schengen), Hoa Kỳ, Canada, Úc và Anh Quốc. Đội ngũ chuyên viên giàu kinh nghiệm trực tiếp thẩm định và tối ưu điểm mạnh hồ sơ cho từng khách hàng.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Quy trình xử lý hồ sơ 4 bước chuyên nghiệp</h2>
<!-- /wp:heading -->
<!-- wp:list {"ordered":true} -->
<ol>
    <li><strong>Tiếp nhận & Thẩm định:</strong> Đánh giá tỷ lệ đậu và tư vấn bổ sung các giấy tờ pháp lý cần thiết trong 2 giờ làm việc.</li>
    <li><strong>Hoàn thiện biểu mẫu:</strong> Dịch thuật công chứng tư pháp, điền tờ khai trực tuyến chuẩn định dạng Lãnh sự.</li>
    <li><strong>Đặt lịch hẹn & Luyện phỏng vấn:</strong> Hướng dẫn chi tiết kỹ năng trả lời phỏng vấn tự tin, trung thực.</li>
    <li><strong>Nhận kết quả & Bàn giao visa:</strong> Nhận hộ chiếu kèm visa tận nhà hoặc qua chuyển phát nhanh bảo đảm.</li>
</ol>
<!-- /wp:list -->
HTML
            ],
            [
                'id'        => 302,
                'title'     => 'Khách Sạn & Combo Du Lịch Nghỉ Dưỡng Cao Cấp',
                'slug'      => 'khach-san-combo-du-lich-nghi-duong',
                'post_date' => '2026-09-01 09:30:00',
                'content'   => <<<HTML
<!-- wp:paragraph -->
<p>Trải nghiệm kỳ nghỉ linh hoạt tự do với chuỗi gói sản phẩm Combo (Vé máy bay khứ hồi + Khách sạn / Resort 4-5 sao) tại các điểm đến hot nhất: Phú Quốc, Nha Trang, Đà Nẵng, Quy Nhơn, Đà Lạt và Hạ Long. Tiết kiệm lên tới 35% so với đặt dịch vụ lẻ.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Đặc quyền dành riêng cho khách hàng đặt Combo</h2>
<!-- /wp:heading -->
<!-- wp:list -->
<ul>
    <li>Hàng không chất lượng cao với giờ bay đẹp nhất trong ngày.</li>
    <li>Xe riêng đón tiễn sân bay về resort hai chiều không phát sinh chi phí.</li>
    <li>Buffet sáng quốc tế hàng ngày và miễn phí sử dụng hồ bơi vô cực, phòng gym.</li>
    <li>Ưu đãi giảm giá 20% các dịch vụ spa, ẩm thực và vé vui chơi giải trí liên kết.</li>
</ul>
<!-- /wp:list -->
HTML
            ],
            [
                'id'        => 303,
                'title'     => 'Dịch Vụ Thuê Xe Du Lịch Đời Mới 4 - 45 Chỗ',
                'slug'      => 'dich-vu-thue-xe-du-lich-doi-moi',
                'post_date' => '2026-09-01 10:00:00',
                'content'   => <<<HTML
<!-- wp:paragraph -->
<p>Cung cấp dịch vụ cho thuê xe du lịch chất lượng cao, xe hoa cưới, xe đón tiễn sân bay và phục vụ chuyên gia đối tác doanh nghiệp. Toàn bộ xe đều là xe đời mới (2022 - 2026), nội thất tiện nghi sang trọng và bảo dưỡng định kỳ nghiêm ngặt.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Đội ngũ xe đa dạng phục vụ mọi nhu cầu</h2>
<!-- /wp:heading -->
<!-- wp:list -->
<ul>
    <li><strong>Xe 4 - 7 chỗ:</strong> Toyota Camry, Kia Carnival, Hyundai SantaFe phục vụ gia đình nhỏ và khách VIP.</li>
    <li><strong>Xe 16 chỗ:</strong> Ford Transit, Hyundai Solati máy lạnh sâu, ghế ngồi êm ái cho nhóm bạn.</li>
    <li><strong>Xe Limousine 9 - 19 chỗ:</strong> Ghế bọc da chỉnh điện massage, cổng sạc USB, tủ lạnh mini sang trọng.</li>
    <li><strong>Xe 29 - 45 chỗ:</strong> Thaco Universe, Hyundai Universe động cơ mạnh mẽ cho đoàn lớn và công ty.</li>
</ul>
<!-- /wp:list -->
HTML
            ],
            [
                'id'        => 304,
                'title'     => 'Tổ Chức Sự Kiện Teambuilding & Gala Dinner Doanh Nghiệp',
                'slug'      => 'to-chuc-teambuilding-gala-dinner-doanh-nghiep',
                'post_date' => '2026-09-01 10:30:00',
                'content'   => <<<HTML
<!-- wp:paragraph -->
<p>Thiết kế và tổ chức trọn gói các chương trình Teambuilding bãi biển, Teambuilding quân đội huấn luyện kỹ năng lãnh đạo và đêm tiệc Gala Dinner kỷ niệm thành lập doanh nghiệp. Chúng tôi biến mỗi chuyến đi thành hành trình kết nối gắn kết nhân sự bền vững.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Hệ thống thiết bị và năng lực tổ chức chuyên nghiệp</h2>
<!-- /wp:heading -->
<!-- wp:list -->
<ul>
    <li>Kịch bản trò chơi sáng tạo, may đo riêng theo văn hóa và giá trị cốt lõi của doanh nghiệp.</li>
    <li>Đội ngũ MC năng lượng cao, huấn luyện viên Teambuilding chuyên nghiệp và đội ngũ hậu cần tận tâm.</li>
    <li>Hệ thống âm thanh ánh sáng sân khấu, màn hình LED biểu diễn đạt chuẩn sự kiện ngoài trời.</li>
    <li>Flycam, thợ quay phim chụp ảnh chuyên nghiệp ghi lại khoảnh khắc gắn kết của tập thể.</li>
</ul>
<!-- /wp:list -->
HTML
            ],
        ];
    }

    /**
     * Export seed data as a standardized JSON archive.
     *
     * WHY: Serves as an immutable single source of truth for headless applications,
     * seed distributions, and verification pipelines.
     *
     * @param string $output_file Output file path.
     * @return bool True on success.
     */
    public function export_json(string $output_file): bool
    {
        $dataset = $this->generate_dataset();
        $json = json_encode($dataset, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \RuntimeException(sprintf('JSON serialization failed: %s', json_last_error_msg()));
        }

        $dir = dirname($output_file);
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            throw new \RuntimeException(sprintf('Failed to create directory: %s', $dir));
        }

        return file_put_contents($output_file, $json) !== false;
    }

    /**
     * Export seed data as an ANSI SQL dump compatible with both SQLite and MariaDB.
     *
     * WHY: Enables instant database hydration without executing PHP bootstrap or WP-CLI,
     * directly compatible with Bedrock sqlite-database-integration and standard MySQL.
     *
     * @param string $output_file Output SQL file path.
     * @return bool True on success.
     */
    public function export_sql(string $output_file): bool
    {
        $dataset = $this->generate_dataset();
        $sql = [];

        $sql[] = "-- ===========================================================================";
        $sql[] = "-- Gonatour WordPress Seed Data Dump (Tours, Guide Posts, Service Pages)";
        $sql[] = "-- Generated by: Gonatour_Seed_Importer v1.0.0";
        $sql[] = "-- Dual-Engine Compatibility: SQLite 3.x & MariaDB 10.x / MySQL 8.x";
        $sql[] = "-- Target Tables: wp_terms, wp_term_taxonomy, wp_term_relationships, wp_posts, wp_postmeta";
        $sql[] = "-- ===========================================================================";
        $sql[] = "";

        // 1. Taxonomies (wp_terms & wp_term_taxonomy)
        $sql[] = "-- ---------------------------------------------------------------------------";
        $sql[] = "-- 1. Taxonomies: tour_type, tour_destination, category";
        $sql[] = "-- ---------------------------------------------------------------------------";

        $term_map = []; // slug => term_id
        $tax_map  = []; // slug => term_taxonomy_id

        $term_id_seq = 100;
        $tt_id_seq   = 100;

        foreach (['tour_type', 'tour_destination', 'category'] as $taxonomy) {
            foreach ($dataset['taxonomies'][$taxonomy] as $item) {
                $term_id_seq++;
                $tt_id_seq++;
                $name        = $this->escape_sql($item['name']);
                $slug        = $this->escape_sql($item['slug']);
                $desc        = $this->escape_sql($item['description'] ?? '');
                $parent_id   = isset($item['parent_slug']) && isset($term_map[$item['parent_slug']]) ? $term_map[$item['parent_slug']] : 0;

                $term_map[$item['slug']] = $term_id_seq;
                $tax_map[$item['slug']]  = $tt_id_seq;

                $sql[] = sprintf(
                    "INSERT INTO wp_terms (term_id, name, slug, term_group) VALUES (%d, '%s', '%s', 0);",
                    $term_id_seq,
                    $name,
                    $slug
                );
                $sql[] = sprintf(
                    "INSERT INTO wp_term_taxonomy (term_taxonomy_id, term_id, taxonomy, description, parent, count) VALUES (%d, %d, '%s', '%s', %d, 0);",
                    $tt_id_seq,
                    $term_id_seq,
                    $taxonomy,
                    $desc,
                    $parent_id
                );
            }
        }
        $sql[] = "";

        // 2. Tours (CPT 'tour')
        $sql[] = "-- ---------------------------------------------------------------------------";
        $sql[] = "-- 2. Custom Post Type: tour (10 Production Records)";
        $sql[] = "-- ---------------------------------------------------------------------------";

        $meta_id_seq = 5000;

        foreach ($dataset['tours'] as $tour) {
            $post_id   = $tour['id'];
            $title     = $this->escape_sql($tour['title']);
            $slug      = $this->escape_sql($tour['slug']);
            $content   = $this->build_tour_content_html($tour);
            $content_e = $this->escape_sql($content);
            $excerpt_e = $this->escape_sql($tour['highlights'][0] ?? '');
            $date      = '2026-09-20 10:00:00';

            $sql[] = sprintf(
                "INSERT INTO wp_posts (ID, post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, comment_count) VALUES (%d, 1, '%s', '%s', '%s', '%s', '%s', 'publish', 'closed', 'closed', '', '%s', '', '', '%s', '%s', '', 0, 'http://localhost/tours/%s', 0, 'tour', '', 0);",
                $post_id,
                $date,
                $date,
                $content_e,
                $title,
                $excerpt_e,
                $slug,
                $date,
                $date,
                $slug
            );

            // Assign taxonomy relationships
            if (isset($tax_map[$tour['tour_type_slug']])) {
                $sql[] = sprintf(
                    "INSERT INTO wp_term_relationships (object_id, term_taxonomy_id, term_order) VALUES (%d, %d, 0);",
                    $post_id,
                    $tax_map[$tour['tour_type_slug']]
                );
            }
            if (isset($tax_map[$tour['destination_slug']])) {
                $sql[] = sprintf(
                    "INSERT INTO wp_term_relationships (object_id, term_taxonomy_id, term_order) VALUES (%d, %d, 0);",
                    $post_id,
                    $tax_map[$tour['destination_slug']]
                );
            }

            // Post Meta mappings
            $departure_dates_str = implode("\n", array_map(static fn($d) => $d['departure_date'], $tour['departures']));
            $itinerary_json      = json_encode($tour['daily_itinerary'], JSON_UNESCAPED_UNICODE);
            $departures_json     = json_encode($tour['departures'], JSON_UNESCAPED_UNICODE);
            $inclusions_json     = json_encode($tour['inclusions'], JSON_UNESCAPED_UNICODE);
            $exclusions_json     = json_encode($tour['exclusions'], JSON_UNESCAPED_UNICODE);
            $terms_json          = json_encode($tour['cancellation_terms'], JSON_UNESCAPED_UNICODE);

            $meta_entries = [
                '_tour_code'               => $tour['tour_code'],
                '_tour_duration'           => $tour['duration_label'],
                '_tour_departure_city'     => $tour['departure_city'],
                '_tour_transportation'     => $tour['transportation'],
                '_tour_price_adult'        => (string) $tour['adult_price'],
                '_tour_price_child'        => (string) $tour['child_price'],
                '_tour_price_infant'       => (string) $tour['infant_price'],
                '_tour_price_single_sup'   => (string) $tour['single_room_supplement'],
                '_tour_departure_dates'    => $departure_dates_str,
                '_tour_itinerary'          => $itinerary_json,
                '_tour_duration_days'      => (string) $tour['duration_days'],
                '_tour_duration_nights'    => (string) $tour['duration_nights'],
                '_tour_destination_primary'=> $tour['destination_primary'],
                '_tour_destination_country'=> $tour['destination_country'],
                '_tour_hotel_stars'        => $tour['hotel_stars'],
                '_tour_status'             => $tour['tour_status'],
                '_tour_departures'         => $departures_json,
                '_tour_inclusions'         => $inclusions_json,
                '_tour_exclusions'         => $exclusions_json,
                '_tour_cancellation_terms' => $terms_json,
            ];

            foreach ($meta_entries as $meta_key => $meta_val) {
                $meta_id_seq++;
                $meta_val_e = $this->escape_sql($meta_val);
                $sql[] = sprintf(
                    "INSERT INTO wp_postmeta (meta_id, post_id, meta_key, meta_value) VALUES (%d, %d, '%s', '%s');",
                    $meta_id_seq,
                    $post_id,
                    $meta_key,
                    $meta_val_e
                );
            }
        }
        $sql[] = "";

        // 3. Travel Guide Posts (post)
        $sql[] = "-- ---------------------------------------------------------------------------";
        $sql[] = "-- 3. Cẩm Nang Du Lịch (Posts)";
        $sql[] = "-- ---------------------------------------------------------------------------";

        foreach ($dataset['posts'] as $post) {
            $post_id   = $post['id'];
            $title     = $this->escape_sql($post['title']);
            $slug      = $this->escape_sql($post['slug']);
            $content_e = $this->escape_sql($post['content']);
            $excerpt_e = $this->escape_sql($post['excerpt']);
            $date      = $post['post_date'];

            $sql[] = sprintf(
                "INSERT INTO wp_posts (ID, post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, comment_count) VALUES (%d, 1, '%s', '%s', '%s', '%s', '%s', 'publish', 'open', 'closed', '', '%s', '', '', '%s', '%s', '', 0, 'http://localhost/%s', 0, 'post', '', 0);",
                $post_id,
                $date,
                $date,
                $content_e,
                $title,
                $excerpt_e,
                $slug,
                $date,
                $date,
                $slug
            );

            if (isset($tax_map[$post['category']])) {
                $sql[] = sprintf(
                    "INSERT INTO wp_term_relationships (object_id, term_taxonomy_id, term_order) VALUES (%d, %d, 0);",
                    $post_id,
                    $tax_map[$post['category']]
                );
            }
        }
        $sql[] = "";

        // 4. Service Pages (page)
        $sql[] = "-- ---------------------------------------------------------------------------";
        $sql[] = "-- 4. Service Pages (Dịch vụ Visa, Khách sạn, Thuê xe, Teambuilding)";
        $sql[] = "-- ---------------------------------------------------------------------------";

        foreach ($dataset['pages'] as $page) {
            $page_id   = $page['id'];
            $title     = $this->escape_sql($page['title']);
            $slug      = $this->escape_sql($page['slug']);
            $content_e = $this->escape_sql($page['content']);
            $date      = $page['post_date'];

            $sql[] = sprintf(
                "INSERT INTO wp_posts (ID, post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, comment_count) VALUES (%d, 1, '%s', '%s', '%s', '%s', '', 'publish', 'closed', 'closed', '', '%s', '', '', '%s', '%s', '', 0, 'http://localhost/%s', 0, 'page', '', 0);",
                $page_id,
                $date,
                $date,
                $content_e,
                $title,
                $slug,
                $date,
                $date,
                $slug
            );
        }

        $dir = dirname($output_file);
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            throw new \RuntimeException(sprintf('Failed to create directory: %s', $dir));
        }

        return file_put_contents($output_file, implode("\n", $sql)) !== false;
    }

    /**
     * Format tour description HTML with Gutenberg blocks and semantic markup.
     *
     * WHY: Provides rich readable content fallback when rendered directly by themes.
     *
     * @param array $tour Tour data dictionary.
     * @return string Structured HTML.
     */
    private function build_tour_content_html(array $tour): string
    {
        $highlights_html = '';
        foreach ($tour['highlights'] as $hl) {
            $highlights_html .= sprintf('<li>%s</li>', htmlspecialchars($hl, ENT_QUOTES, 'UTF-8'));
        }

        $inclusions_html = '';
        foreach ($tour['inclusions'] as $inc) {
            $inclusions_html .= sprintf('<li>%s</li>', htmlspecialchars($inc, ENT_QUOTES, 'UTF-8'));
        }

        $exclusions_html = '';
        foreach ($tour['exclusions'] as $exc) {
            $exclusions_html .= sprintf('<li>%s</li>', htmlspecialchars($exc, ENT_QUOTES, 'UTF-8'));
        }

        return <<<HTML
<!-- wp:paragraph {"lead":true} -->
<p class="tour-intro">Khám phá hành trình <strong>{$tour['title']}</strong> cùng Gonatour. Thời lượng <strong>{$tour['duration_label']}</strong>, khởi hành từ <strong>{$tour['departure_city']}</strong> với tiêu chuẩn lưu trú <strong>{$tour['hotel_stars']}</strong>.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Điểm Nhấn Nổi Bật Của Chương Trình</h2>
<!-- /wp:heading -->
<!-- wp:list -->
<ul>
    {$highlights_html}
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>Dịch Vụ Bao Gồm</h2>
<!-- /wp:heading -->
<!-- wp:list -->
<ul>
    {$inclusions_html}
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>Dịch Vụ Chưa Bao Gồm</h2>
<!-- /wp:heading -->
<!-- wp:list -->
<ul>
    {$exclusions_html}
</ul>
<!-- /wp:list -->
HTML;
    }

    /**
     * Execute seed insertion directly within an active WordPress runtime environment.
     *
     * WHY: Allows live hydration of WordPress database when executed via WP-CLI
     * (wp eval-file scripts/import_gonatour_seeds.php) or web administrator hook.
     *
     * @return array Ingestion execution report.
     * @throws \RuntimeException If WordPress core functions are not present.
     */
    public function execute_wp_import(): array
    {
        if (!function_exists('wp_insert_post') || !function_exists('update_post_meta')) {
            throw new \RuntimeException('WordPress core functions not loaded. Cannot execute runtime database import.');
        }

        $dataset = $this->generate_dataset();
        $imported_tours = 0;
        $imported_posts = 0;
        $imported_pages = 0;
        $term_id_map    = [];

        // 1. Taxonomies
        foreach (['tour_type', 'tour_destination', 'category'] as $taxonomy) {
            foreach ($dataset['taxonomies'][$taxonomy] as $item) {
                $parent_id = 0;
                if (!empty($item['parent_slug']) && isset($term_id_map[$taxonomy][$item['parent_slug']])) {
                    $parent_id = $term_id_map[$taxonomy][$item['parent_slug']];
                }

                $existing = term_exists($item['slug'], $taxonomy);
                if ($existing) {
                    $term_id = is_array($existing) ? (int) $existing['term_id'] : (int) $existing;
                } else {
                    $created = wp_insert_term($item['name'], $taxonomy, [
                        'slug'        => $item['slug'],
                        'description' => $item['description'] ?? '',
                        'parent'      => $parent_id,
                    ]);
                    $term_id = !is_wp_error($created) ? (int) $created['term_id'] : 0;
                }
                $term_id_map[$taxonomy][$item['slug']] = $term_id;
            }
        }

        // 2. Tours
        foreach ($dataset['tours'] as $tour) {
            $existing_post = get_page_by_path($tour['slug'], OBJECT, 'tour');
            $post_args = [
                'post_title'   => $tour['title'],
                'post_name'    => $tour['slug'],
                'post_content' => $this->build_tour_content_html($tour),
                'post_excerpt' => $tour['highlights'][0] ?? '',
                'post_status'  => 'publish',
                'post_type'    => 'tour',
            ];

            if ($existing_post) {
                $post_args['ID'] = $existing_post->ID;
                $post_id = wp_update_post($post_args);
            } else {
                $post_id = wp_insert_post($post_args);
            }

            if (!is_wp_error($post_id) && $post_id > 0) {
                $imported_tours++;
                // Taxonomies
                if (isset($term_id_map['tour_type'][$tour['tour_type_slug']])) {
                    wp_set_object_terms($post_id, [$term_id_map['tour_type'][$tour['tour_type_slug']]], 'tour_type');
                }
                if (isset($term_id_map['tour_destination'][$tour['destination_slug']])) {
                    wp_set_object_terms($post_id, [$term_id_map['tour_destination'][$tour['destination_slug']]], 'tour_destination');
                }

                // Core and rich metadata
                $departure_dates_str = implode("\n", array_map(static fn($d) => $d['departure_date'], $tour['departures']));
                update_post_meta($post_id, '_tour_code', $tour['tour_code']);
                update_post_meta($post_id, '_tour_duration', $tour['duration_label']);
                update_post_meta($post_id, '_tour_departure_city', $tour['departure_city']);
                update_post_meta($post_id, '_tour_transportation', $tour['transportation']);
                update_post_meta($post_id, '_tour_price_adult', (int) $tour['adult_price']);
                update_post_meta($post_id, '_tour_price_child', (int) $tour['child_price']);
                update_post_meta($post_id, '_tour_price_infant', (int) $tour['infant_price']);
                update_post_meta($post_id, '_tour_price_single_sup', (int) $tour['single_room_supplement']);
                update_post_meta($post_id, '_tour_departure_dates', $departure_dates_str);
                update_post_meta($post_id, '_tour_itinerary', wp_json_encode($tour['daily_itinerary'], JSON_UNESCAPED_UNICODE));
                update_post_meta($post_id, '_tour_duration_days', (int) $tour['duration_days']);
                update_post_meta($post_id, '_tour_duration_nights', (int) $tour['duration_nights']);
                update_post_meta($post_id, '_tour_destination_primary', $tour['destination_primary']);
                update_post_meta($post_id, '_tour_destination_country', $tour['destination_country']);
                update_post_meta($post_id, '_tour_hotel_stars', $tour['hotel_stars']);
                update_post_meta($post_id, '_tour_status', $tour['tour_status']);
                update_post_meta($post_id, '_tour_departures', wp_json_encode($tour['departures'], JSON_UNESCAPED_UNICODE));
                update_post_meta($post_id, '_tour_inclusions', wp_json_encode($tour['inclusions'], JSON_UNESCAPED_UNICODE));
                update_post_meta($post_id, '_tour_exclusions', wp_json_encode($tour['exclusions'], JSON_UNESCAPED_UNICODE));
                update_post_meta($post_id, '_tour_cancellation_terms', wp_json_encode($tour['cancellation_terms'], JSON_UNESCAPED_UNICODE));
            }
        }

        // 3. Posts
        foreach ($dataset['posts'] as $post) {
            $existing_post = get_page_by_path($post['slug'], OBJECT, 'post');
            $post_args = [
                'post_title'   => $post['title'],
                'post_name'    => $post['slug'],
                'post_content' => $post['content'],
                'post_excerpt' => $post['excerpt'],
                'post_status'  => 'publish',
                'post_type'    => 'post',
            ];
            if ($existing_post) {
                $post_args['ID'] = $existing_post->ID;
                $post_id = wp_update_post($post_args);
            } else {
                $post_id = wp_insert_post($post_args);
            }
            if (!is_wp_error($post_id) && $post_id > 0) {
                $imported_posts++;
                if (isset($term_id_map['category'][$post['category']])) {
                    wp_set_object_terms($post_id, [$term_id_map['category'][$post['category']]], 'category');
                }
            }
        }

        // 4. Pages
        foreach ($dataset['pages'] as $page) {
            $existing_page = get_page_by_path($page['slug'], OBJECT, 'page');
            $page_args = [
                'post_title'   => $page['title'],
                'post_name'    => $page['slug'],
                'post_content' => $page['content'],
                'post_status'  => 'publish',
                'post_type'    => 'page',
            ];
            if ($existing_page) {
                $page_args['ID'] = $existing_page->ID;
                $page_id = wp_update_post($page_args);
            } else {
                $page_id = wp_insert_post($page_args);
            }
            if (!is_wp_error($page_id) && $page_id > 0) {
                $imported_pages++;
            }
        }

        return [
            'status'         => 'success',
            'imported_tours' => $imported_tours,
            'imported_posts' => $imported_posts,
            'imported_pages' => $imported_pages,
        ];
    }

    /**
     * Escape strings for standard SQL literal values.
     *
     * WHY: Prevents SQL syntax breakage across SQLite and MariaDB dialects.
     *
     * @param string $val Raw string.
     * @return string Escaped string.
     */
    private function escape_sql(string $val): string
    {
        return str_replace("'", "''", $val);
    }

    /**
     * Simulate REST API catalog endpoint (/wp-json/iz-tour/v1/tours).
     *
     * WHY: Validates querying, pricing filters, taxonomy filters, and payload structure
     * in test environments without requiring active HTTP daemon.
     *
     * @param array $params Query filter arguments.
     * @return array Simulating WP_REST_Response array.
     */
    public function simulate_rest_catalog(array $params = []): array
    {
        $dataset = $this->generate_dataset();
        $tours   = $dataset['tours'];

        // Filter: tour_type
        if (!empty($params['tour_type'])) {
            $tours = array_filter($tours, static function ($t) use ($params) {
                return $t['tour_type_slug'] === $params['tour_type'];
            });
        }

        // Filter: destination
        if (!empty($params['destination'])) {
            $tours = array_filter($tours, static function ($t) use ($params) {
                return $t['destination_slug'] === $params['destination'];
            });
        }

        // Filter: min_price
        if (!empty($params['min_price'])) {
            $min = (int) $params['min_price'];
            $tours = array_filter($tours, static fn($t) => $t['adult_price'] >= $min);
        }

        // Filter: max_price
        if (!empty($params['max_price'])) {
            $max = (int) $params['max_price'];
            $tours = array_filter($tours, static fn($t) => $t['adult_price'] <= $max);
        }

        // Filter: duration
        if (!empty($params['duration'])) {
            $tours = array_filter($tours, static function ($t) use ($params) {
                return stripos($t['duration_label'], (string) $params['duration']) !== false;
            });
        }

        // Filter: search keyword
        if (!empty($params['search'])) {
            $kw = (string) $params['search'];
            $tours = array_filter($tours, static function ($t) use ($kw) {
                return stripos($t['title'], $kw) !== false || stripos($t['tour_code'], $kw) !== false;
            });
        }

        $items = [];
        foreach ($tours as $t) {
            $items[] = [
                'id'               => $t['id'],
                'title'            => $t['title'],
                'slug'             => $t['slug'],
                'excerpt'          => $t['highlights'][0] ?? '',
                'permalink'        => 'http://localhost/tours/' . $t['slug'],
                'thumbnail_url'    => '',
                'tour_code'        => $t['tour_code'],
                'duration'         => $t['duration_label'],
                'departure_city'   => $t['departure_city'],
                'transportation'   => $t['transportation'],
                'price_adult'      => $t['adult_price'],
                'price_child'      => $t['child_price'],
                'price_infant'     => $t['infant_price'],
                'price_single_sup' => $t['single_room_supplement'],
                'departure_dates'  => array_map(static fn($d) => $d['departure_date'], $t['departures']),
                'itinerary'        => $t['daily_itinerary'],
                'destinations'     => [['slug' => $t['destination_slug'], 'name' => $t['destination_primary']]],
                'tour_types'       => [['slug' => $t['tour_type_slug'], 'name' => $t['market_type'] === 1 ? 'Trong nước' : 'Quốc tế']],
            ];
        }

        return [
            'items'        => array_values($items),
            'total'        => count($items),
            'total_pages'  => 1,
            'current_page' => 1,
            'per_page'     => count($items),
        ];
    }

    /**
     * Simulate server-side booking pricing calculation.
     *
     * WHY: Verifies financial integrity formula:
     * total = (adults * adult_price) + (children * child_price) + (infants * infant_price) + (single_rooms * single_supplement).
     * Matches Gonatour pricing engine specifications.
     *
     * @param string $tour_code    Target tour SKU.
     * @param int    $adults       Count of adults (>= 12y).
     * @param int    $children     Count of children (2 - 11y).
     * @param int    $infants      Count of infants (< 2y).
     * @param int    $single_rooms Count of single rooms.
     * @return array Financial breakdown.
     */
    public function simulate_booking_calculation(string $tour_code, int $adults, int $children = 0, int $infants = 0, int $single_rooms = 0): array
    {
        $dataset = $this->generate_dataset();
        $tour = null;
        foreach ($dataset['tours'] as $t) {
            if ($t['tour_code'] === $tour_code) {
                $tour = $t;
                break;
            }
        }

        if (!$tour) {
            throw new \InvalidArgumentException(sprintf('Tour code "%s" not found in dataset.', $tour_code));
        }

        $adult_subtotal  = $adults * $tour['adult_price'];
        $child_subtotal  = $children * $tour['child_price'];
        $infant_subtotal = $infants * $tour['infant_price'];
        $single_sup_total= $single_rooms * $tour['single_room_supplement'];
        $grand_total     = $adult_subtotal + $child_subtotal + $infant_subtotal + $single_sup_total;

        $deposit_rate    = 0.50; // 50% deposit per Gonatour rules
        $deposit_due     = (int) round($grand_total * $deposit_rate);

        return [
            'tour_code'           => $tour_code,
            'tour_title'          => $tour['title'],
            'adults'              => $adults,
            'adult_unit_price'    => $tour['adult_price'],
            'adult_subtotal'      => $adult_subtotal,
            'children'            => $children,
            'child_unit_price'    => $tour['child_price'],
            'child_subtotal'      => $child_subtotal,
            'infants'             => $infants,
            'infant_unit_price'   => $tour['infant_price'],
            'infant_subtotal'     => $infant_subtotal,
            'single_rooms'        => $single_rooms,
            'single_room_unit'    => $tour['single_room_supplement'],
            'single_room_subtotal'=> $single_sup_total,
            'grand_total'         => $grand_total,
            'deposit_due_50pct'   => $deposit_due,
            'currency'            => 'VND',
        ];
    }
}

// -----------------------------------------------------------------------------
// Direct CLI Execution Handler
// -----------------------------------------------------------------------------
if (php_sapi_name() === 'cli' && isset($argv[0]) && realpath($argv[0]) === realpath(__FILE__)) {
    $repo_root = dirname(__DIR__);
    $json_file = $repo_root . DIRECTORY_SEPARATOR . 'seeds' . DIRECTORY_SEPARATOR . 'gonatour_seeds.json';
    $sql_file  = $repo_root . DIRECTORY_SEPARATOR . 'seeds' . DIRECTORY_SEPARATOR . 'gonatour_seeds.sql';

    echo "=== Gonatour Seed Importer & Architecture Generator ===\n";

    try {
        $importer = new Gonatour_Seed_Importer();
        $model = $importer->load_model();
        echo sprintf("[OK] Loaded data model: %s (v%s)\n", $model['meta']['title'], $model['meta']['version']);

        $importer->export_json($json_file);
        echo sprintf("[OK] Exported JSON Seed: %s (%s KB)\n", $json_file, number_format(filesize($json_file) / 1024, 1));

        $importer->export_sql($sql_file);
        echo sprintf("[OK] Exported SQL Dump:  %s (%s KB)\n", $sql_file, number_format(filesize($sql_file) / 1024, 1));

        echo "[OK] Generation completed successfully.\n";
        exit(0);
    } catch (\Throwable $e) {
        fwrite(STDERR, sprintf("[ERROR] %s\n", $e->getMessage()));
        exit(1);
    }
}
