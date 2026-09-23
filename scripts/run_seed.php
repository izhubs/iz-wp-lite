<?php
/**
 * Test & Verification Runner for Gonatour Seed Importer.
 *
 * Runs data generation, schema validation, REST API simulation,
 * financial price calculation verification, and SQLite in-memory hydration.
 *
 * PHP version 8.2+
 *
 * DECISION: In-memory SQLite verification for SQL seed dump.
 * WHY: Verifies that seeds/gonatour_seeds.sql executes without syntax errors on SQLite
 * (the default engine of iz-wp-lite) before committing or deploying to staging.
 *
 * @package           IZTourEngine\Scripts
 * @author            Seed Data & Integration QA Engineer
 * @license           GPL-2.0-or-later
 */

declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'import_gonatour_seeds.php';

use IZTourEngine\Seeds\Gonatour_Seed_Importer;

final class Gonatour_Test_Runner
{
    private int $tests_total  = 0;
    private int $tests_passed = 0;
    private int $tests_failed = 0;
    private array $failures   = [];

    private string $root_dir;
    private string $json_file;
    private string $sql_file;

    /**
     * Constructor.
     *
     * WHY: Initializes file paths and test harness environment.
     */
    public function __construct()
    {
        $this->root_dir  = dirname(__DIR__);
        $this->json_file = $this->root_dir . DIRECTORY_SEPARATOR . 'seeds' . DIRECTORY_SEPARATOR . 'gonatour_seeds.json';
        $this->sql_file  = $this->root_dir . DIRECTORY_SEPARATOR . 'seeds' . DIRECTORY_SEPARATOR . 'gonatour_seeds.sql';
    }

    /**
     * Run all test suites and output RFC-style telemetry report.
     *
     * @return int Process exit code (0 = success, 1 = failure).
     */
    public function run(): int
    {
        $start_time = microtime(true);
        echo "===============================================================================\n";
        echo " Gonatour Tour Engine - Seed Data Importer & Integration QA Test Suite\n";
        echo " Runtime: PHP " . PHP_VERSION . " (" . PHP_OS . ")\n";
        echo "===============================================================================\n\n";

        // Suite 1: Generation & Artifact Export
        $this->suite_artifact_generation();

        // Suite 2: Schema & Entity Data Integrity
        $this->suite_data_integrity();

        // Suite 3: REST API Catalog Filter Simulation
        $this->suite_rest_catalog_simulation();

        // Suite 4: Commercial Pricing & Deposit Formulas
        $this->suite_pricing_engine();

        // Suite 5: SQLite Database Hydration Verification
        $this->suite_sqlite_hydration();

        $elapsed = round((microtime(true) - $start_time) * 1000, 2);

        echo "\n-------------------------------------------------------------------------------\n";
        echo sprintf("RESULTS: Total Tests: %d | Passed: %d | Failed: %d | Latency: %s ms\n", $this->tests_total, $this->tests_passed, $this->tests_failed, $elapsed);
        echo "-------------------------------------------------------------------------------\n";

        if ($this->tests_failed > 0) {
            echo "\nFAILURES DETECTED:\n";
            foreach ($this->failures as $idx => $fail) {
                echo sprintf("  [%d] %s\n", $idx + 1, $fail);
            }
            return 1;
        }

        echo "\nSTATUS: [PASS] All test assertions verified successfully.\n";
        return 0;
    }

    /**
     * Assert condition and record telemetry.
     *
     * @param bool   $condition Assertion result.
     * @param string $message   Test description.
     */
    private function assert(bool $condition, string $message): void
    {
        $this->tests_total++;
        if ($condition) {
            $this->tests_passed++;
            echo sprintf("  [PASS] %s\n", $message);
        } else {
            $this->tests_failed++;
            $this->failures[] = $message;
            echo sprintf("  [FAIL] %s\n", $message);
        }
    }

    /**
     * Suite 1: Artifact Generation & Export.
     */
    private function suite_artifact_generation(): void
    {
        echo "[SUITE 1] Artifact Generation & Seed Export\n";

        $importer = new Gonatour_Seed_Importer();
        $model = $importer->load_model();
        $this->assert(!empty($model['meta']['title']), 'Data model successfully loaded from docs/gonatour_data_model.json');

        $json_ok = $importer->export_json($this->json_file);
        $this->assert($json_ok && file_exists($this->json_file), sprintf('Export seeds/gonatour_seeds.json (%s KB)', number_format(filesize($this->json_file) / 1024, 1)));

        $sql_ok = $importer->export_sql($this->sql_file);
        $this->assert($sql_ok && file_exists($this->sql_file), sprintf('Export seeds/gonatour_seeds.sql (%s KB)', number_format(filesize($this->sql_file) / 1024, 1)));
        echo "\n";
    }

    /**
     * Suite 2: Schema & Entity Data Integrity.
     */
    private function suite_data_integrity(): void
    {
        echo "[SUITE 2] Schema & Entity Data Integrity\n";

        $raw_json = file_get_contents($this->json_file);
        $data = json_decode($raw_json, true);

        $this->assert(is_array($data), 'JSON seed parses without errors');
        $this->assert(isset($data['tours']) && count($data['tours']) === 10, 'Contains exactly 10 production-quality tour records');
        $this->assert(isset($data['posts']) && count($data['posts']) === 4, 'Contains 4 travel guide posts');
        $this->assert(isset($data['pages']) && count($data['pages']) === 4, 'Contains 4 service pages');

        // Check required 10 tour codes
        $expected_codes = [
            'GNT4NHV-0112' => 'Nha Trang 4N3D',
            'TL5NTAL-3009' => 'Thái Lan 5N4D',
            'GNT4DNH-2026' => 'Đà Nẵng 4N3D',
            'GNT3PQ-2026'  => 'Phú Quốc 3N2Đ',
            'GNT3SP-2026'  => 'Sapa 3N2Đ',
            'GNT2MT-2026'  => 'Miền Tây 2N1Đ',
            'GSM4NTN-2409' => 'Singapore - Malaysia 4N3D',
            'NB5NTN-2409'  => 'Nhật Bản 5N5Đ',
            'HQ5NSE-2026'  => 'Hàn Quốc 5N4D',
            'EU10NVVT-0210'=> 'Châu Âu 10N9Đ',
        ];

        $found_codes = array_column($data['tours'], 'tour_code');
        foreach ($expected_codes as $code => $label) {
            $this->assert(in_array($code, $found_codes, true), sprintf('Verified tour code: %s (%s)', $code, $label));
        }

        // Check tour structure completeness
        $all_complete = true;
        foreach ($data['tours'] as $t) {
            if (empty($t['tour_code']) || empty($t['duration_label']) || empty($t['departure_city'])
                || count($t['departures']) !== 3 || empty($t['daily_itinerary']) || empty($t['inclusions'])
                || empty($t['exclusions']) || empty($t['cancellation_terms'])) {
                $all_complete = false;
                break;
            }
        }
        $this->assert($all_complete, 'All 10 tours contain complete meta, 3 departures, daily itinerary, inclusions, and policies');
        echo "\n";
    }

    /**
     * Suite 3: REST API Catalog Filter Simulation.
     */
    private function suite_rest_catalog_simulation(): void
    {
        echo "[SUITE 3] REST API Catalog Endpoint Simulation (/wp-json/iz-tour/v1/tours)\n";

        $importer = new Gonatour_Seed_Importer();

        // 1. All tours
        $res = $importer->simulate_rest_catalog();
        $this->assert($res['total'] === 10, 'Default catalog query returns 10 tours');

        // 2. Filter by destination
        $res_nt = $importer->simulate_rest_catalog(['destination' => 'nha-trang']);
        $this->assert($res_nt['total'] === 1 && $res_nt['items'][0]['tour_code'] === 'GNT4NHV-0112', 'Filter destination=nha-trang returns GNT4NHV-0112');

        // 3. Filter by tour_type (Domestic vs Outbound)
        $res_dom = $importer->simulate_rest_catalog(['tour_type' => 'trong-nuoc']);
        $this->assert($res_dom['total'] === 5, 'Filter tour_type=trong-nuoc returns 5 domestic tours');

        $res_intl = $importer->simulate_rest_catalog(['tour_type' => 'quoc-te']);
        $this->assert($res_intl['total'] === 5, 'Filter tour_type=quoc-te returns 5 international tours');

        // 4. Filter by price range
        $res_price = $importer->simulate_rest_catalog(['min_price' => 10000000, 'max_price' => 40000000]);
        $this->assert($res_price['total'] === 2, 'Filter price 10M - 40M returns 2 tours (Hàn Quốc & Nhật Bản)');

        // 5. Keyword search
        $res_search = $importer->simulate_rest_catalog(['search' => 'Fansipan']);
        $this->assert($res_search['total'] === 1 && $res_search['items'][0]['tour_code'] === 'GNT3SP-2026', 'Keyword search "Fansipan" returns Sapa tour');
        echo "\n";
    }

    /**
     * Suite 4: Commercial Pricing & Deposit Formulas.
     */
    private function suite_pricing_engine(): void
    {
        echo "[SUITE 4] Commercial Pricing & Deposit Calculation Verification\n";

        $importer = new Gonatour_Seed_Importer();

        // Case 1: Nha Trang 4N3D (GNT4NHV-0112: 3,197,000 VND / child 1,990,000 / infant 0)
        // Order: 2 Adults + 1 Child + 1 Infant + 0 Single room
        // Expected Total: (2 * 3,197,000) + (1 * 1,990,000) + 0 = 6,394,000 + 1,990,000 = 8,384,000 VND
        // Deposit 50%: 4,192,000 VND
        $calc1 = $importer->simulate_booking_calculation('GNT4NHV-0112', 2, 1, 1, 0);
        $this->assert($calc1['grand_total'] === 8384000, sprintf('Nha Trang order: 2 adults + 1 child + 1 infant = %s VND', number_format($calc1['grand_total'])));
        $this->assert($calc1['deposit_due_50pct'] === 4192000, sprintf('Nha Trang 50%% deposit = %s VND', number_format($calc1['deposit_due_50pct'])));

        // Case 2: Thái Lan 5N4D (TL5NTAL-3009: 7,790,000 VND / single sup 2,400,000)
        // Order: 1 Adult + 0 Child + 0 Infant + 1 Single room
        // Expected Total: 7,790,000 + 2,400,000 = 10,190,000 VND
        // Deposit 50%: 5,095,000 VND
        $calc2 = $importer->simulate_booking_calculation('TL5NTAL-3009', 1, 0, 0, 1);
        $this->assert($calc2['grand_total'] === 10190000, sprintf('Thái Lan order: 1 adult + single room supplement = %s VND', number_format($calc2['grand_total'])));
        $this->assert($calc2['deposit_due_50pct'] === 5095000, sprintf('Thái Lan 50%% deposit = %s VND', number_format($calc2['deposit_due_50pct'])));

        // Case 3: Châu Âu 4 Nước (EU10NVVT-0210: 79,900,000 VND / child 69,900,000 / infant 18,000,000 / single sup 16,500,000)
        // Order: 2 Adults + 1 Child + 1 Infant + 1 Single room
        // Expected Total: (2 * 79,900,000) + 69,900,000 + 18,000,000 + 16,500,000 = 159,800,000 + 69,900,000 + 18,000,000 + 16,500,000 = 264,200,000 VND
        // Deposit 50%: 132,100,000 VND
        $calc3 = $importer->simulate_booking_calculation('EU10NVVT-0210', 2, 1, 1, 1);
        $this->assert($calc3['grand_total'] === 264200000, sprintf('Châu Âu order: 2 adults + 1 child + 1 infant + 1 single room = %s VND', number_format($calc3['grand_total'])));
        $this->assert($calc3['deposit_due_50pct'] === 132100000, sprintf('Châu Âu 50%% deposit = %s VND', number_format($calc3['deposit_due_50pct'])));
        echo "\n";
    }

    /**
     * Suite 5: SQLite Database Hydration Verification.
     *
     * WHY: Proves that seeds/gonatour_seeds.sql can be executed directly
     * into an SQLite database (the runtime database engine of iz-wp-lite).
     */
    private function suite_sqlite_hydration(): void
    {
        echo "[SUITE 5] SQLite In-Memory Database Hydration (Dialect & Syntax Validation)\n";

        if (!extension_loaded('pdo_sqlite')) {
            echo "  [WARN] pdo_sqlite extension not loaded in CLI. Skipping live SQLite verification.\n";
            return;
        }

        try {
            $pdo = new \PDO('sqlite::memory:');
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // Create schema matching standard WordPress tables
            $schema = <<<SQL
CREATE TABLE wp_terms (
    term_id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    slug TEXT NOT NULL,
    term_group INTEGER DEFAULT 0
);

CREATE TABLE wp_term_taxonomy (
    term_taxonomy_id INTEGER PRIMARY KEY AUTOINCREMENT,
    term_id INTEGER NOT NULL,
    taxonomy TEXT NOT NULL,
    description TEXT,
    parent INTEGER DEFAULT 0,
    count INTEGER DEFAULT 0
);

CREATE TABLE wp_term_relationships (
    object_id INTEGER NOT NULL,
    term_taxonomy_id INTEGER NOT NULL,
    term_order INTEGER DEFAULT 0,
    PRIMARY KEY (object_id, term_taxonomy_id)
);

CREATE TABLE wp_posts (
    ID INTEGER PRIMARY KEY AUTOINCREMENT,
    post_author INTEGER DEFAULT 1,
    post_date TEXT NOT NULL,
    post_date_gmt TEXT NOT NULL,
    post_content TEXT,
    post_title TEXT NOT NULL,
    post_excerpt TEXT,
    post_status TEXT DEFAULT 'publish',
    comment_status TEXT DEFAULT 'closed',
    ping_status TEXT DEFAULT 'closed',
    post_password TEXT DEFAULT '',
    post_name TEXT NOT NULL,
    to_ping TEXT DEFAULT '',
    pinged TEXT DEFAULT '',
    post_modified TEXT NOT NULL,
    post_modified_gmt TEXT NOT NULL,
    post_content_filtered TEXT DEFAULT '',
    post_parent INTEGER DEFAULT 0,
    guid TEXT NOT NULL,
    menu_order INTEGER DEFAULT 0,
    post_type TEXT NOT NULL,
    post_mime_type TEXT DEFAULT '',
    comment_count INTEGER DEFAULT 0
);

CREATE TABLE wp_postmeta (
    meta_id INTEGER PRIMARY KEY AUTOINCREMENT,
    post_id INTEGER NOT NULL,
    meta_key TEXT NOT NULL,
    meta_value TEXT
);
SQL;
            $pdo->exec($schema);
            $this->assert(true, 'Created WordPress schema in SQLite memory database');

            // Read seeds/gonatour_seeds.sql
            $sql_content = file_get_contents($this->sql_file);

            // Execute SQL statements in a transaction
            $pdo->beginTransaction();
            $pdo->exec($sql_content);
            $pdo->commit();
            $this->assert(true, 'Executed seeds/gonatour_seeds.sql into SQLite with zero syntax errors');

            // Verify counts
            $stmt_tours = $pdo->query("SELECT COUNT(*) FROM wp_posts WHERE post_type = 'tour'");
            $count_tours = (int) $stmt_tours->fetchColumn();
            $this->assert($count_tours === 10, sprintf('SQLite queried %d tours in wp_posts table', $count_tours));

            $stmt_posts = $pdo->query("SELECT COUNT(*) FROM wp_posts WHERE post_type = 'post'");
            $count_posts = (int) $stmt_posts->fetchColumn();
            $this->assert($count_posts === 4, sprintf('SQLite queried %d guide posts in wp_posts table', $count_posts));

            $stmt_pages = $pdo->query("SELECT COUNT(*) FROM wp_posts WHERE post_type = 'page'");
            $count_pages = (int) $stmt_pages->fetchColumn();
            $this->assert($count_pages === 4, sprintf('SQLite queried %d service pages in wp_posts table', $count_pages));

            // Verify meta lookup for Tour 101 (Nha Trang)
            $stmt_meta = $pdo->prepare("SELECT meta_value FROM wp_postmeta WHERE post_id = 101 AND meta_key = '_tour_price_adult'");
            $stmt_meta->execute();
            $nha_trang_price = (int) $stmt_meta->fetchColumn();
            $this->assert($nha_trang_price === 3197000, sprintf('SQLite queried Tour 101 price = %s VND', number_format($nha_trang_price)));

            // Verify term relationships
            $stmt_rel = $pdo->query("SELECT COUNT(*) FROM wp_term_relationships");
            $count_rel = (int) $stmt_rel->fetchColumn();
            $this->assert($count_rel >= 20, sprintf('SQLite queried %d taxonomy term relationships', $count_rel));

        } catch (\Throwable $e) {
            $this->assert(false, sprintf('SQLite execution failed: %s', $e->getMessage()));
        }
        echo "\n";
    }
}

// CLI entry point
if (php_sapi_name() === 'cli') {
    $runner = new Gonatour_Test_Runner();
    exit($runner->run());
}
