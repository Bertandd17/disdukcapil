<?php

namespace Tests\Unit\Services;

use App\Services\KtpOcrParsingService;
use App\Services\AdvancedKtpOcrParsingService;
use Tests\TestCase;

/**
 * KtpOcrParsingService Edge Case Unit Tests
 *
 * Unit tests for parsing service edge cases:
 * - Date format variations
 * - NIK with OCR misreads
 * - Multi-line address parsing
 * - Special characters in names
 * - RT/RW format variations
 * - Empty/null field handling
 * - Indonesian character encoding
 *
 * Disdukcapil Project
 */
class KtpOcrParsingServiceEdgeTest extends TestCase
{
    private KtpOcrParsingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new KtpOcrParsingService();
    }

    // ========================================================================
    // DATE FORMAT VARIATIONS
    // ========================================================================

    /**
     * Date format: DD-MM-YYYY (standard)
     */
    public function test_date_format_dd_mm_yyyy_with_dash(): void
    {
        $text = "Tanggal Lahir : 17-08-1990";
        $parsed = $this->service->parse($text);

        $this->assertEquals('17-08-1990', $parsed['tanggal_lahir']);
    }

    /**
     * Date format: DD/MM/YYYY with slash
     */
    public function test_date_format_dd_slash_mm_slash_yyyy(): void
    {
        $text = "Tanggal Lahir : 17/08/1990";
        $parsed = $this->service->parse($text);

        $this->assertEquals('17-08-1990', $parsed['tanggal_lahir']);
    }

    /**
     * Date format: DD.MM.YYYY with dot
     */
    public function test_date_format_dd_dot_mm_dot_yyyy(): void
    {
        $text = "Tanggal Lahir : 17.08.1990";
        $parsed = $this->service->parse($text);

        $this->assertEquals('17-08-1990', $parsed['tanggal_lahir']);
    }

    /**
     * Date format with single digit day/month.
     */
    public function test_date_format_single_digit_day_month(): void
    {
        $text = "Tanggal Lahir : 5-6-1990";
        $parsed = $this->service->parse($text);

        $this->assertEquals('05-06-1990', $parsed['tanggal_lahir']);
    }

    /**
     * Date format: DD MM YYYY with spaces.
     */
    public function test_date_format_with_spaces(): void
    {
        $text = "Tanggal Lahir : 17 08 1990";
        $parsed = $this->service->parse($text);

        // Should handle spaced format
        $this->assertNotEmpty($parsed['tanggal_lahir']);
    }

    /**
     * Invalid date format should not extract.
     */
    public function test_invalid_date_format_not_extracted(): void
    {
        $text = "Tanggal Lahir : 32-13-1990"; // Invalid day and month
        $parsed = $this->service->parse($text);

        $this->assertEquals('', $parsed['tanggal_lahir']);
    }

    // ========================================================================
    // NIK WITH OCR MISREADS
    // ========================================================================

    /**
     * NIK with O misread as 0.
     */
    public function test_nik_with_o_misread_as_zero(): void
    {
        $text = "NIK : 3175OOOO00000001";
        $parsed = $this->service->parse($text);

        // O harus di-normalize ke 0
        $this->assertEquals('3175000000000001', $parsed['nik']);
    }

    /**
     * NIK with multiple O misreads.
     */
    public function test_nik_with_multiple_o_misreads(): void
    {
        $text = "NIK : 1201O17O89OOOOO1";
        $parsed = $this->service->parse($text);

        $this->assertEquals('120101708900001', $parsed['nik']);
    }

    /**
     * NIK with I misread as 1.
     */
    public function test_nik_with_i_misread_as_one(): void
    {
        $text = "NIK : 3175IIII00000001";
        $parsed = $this->service->parse($text);

        $this->assertEquals('3175111100000001', $parsed['nik']);
    }

    /**
     * NIK with l (lowercase L) misread as 1.
     */
    public function test_nik_with_lowercase_l_misread(): void
    {
        $text = "NIK : 3175lll000000001";
        $parsed = $this->service->parse($text);

        $this->assertEquals('3175111100000001', $parsed['nik']);
    }

    /**
     * NIK with mixed misreads.
     */
    public function test_nik_with_mixed_misreads(): void
    {
        $text = "NIK : 3175O0000I000OO1";
        $parsed = $this->service->parse($text);

        $this->assertEquals('31750000100001', $parsed['nik']);
    }

    /**
     * NIK without label - standalone 16 digits.
     */
    public function test_nik_standalone_16_digits(): void
    {
        $text = "BAMBANG SUPRIYANTO\n3175000000000001\nJL. MERDEKA";
        $parsed = $this->service->parse($text);

        $this->assertEquals('3175000000000001', $parsed['nik']);
    }

    // ========================================================================
    // MULTI-LINE ADDRESS PARSING
    // ========================================================================

    /**
     * Multi-line address with multiple components.
     */
    public function test_multi_line_address(): void
    {
        $text = "Alamat : JL. PENDIDIKAN NO. 12
RT/RW : 001/005
Kel/Desa : SIANTAR MARToba
Kecamatan : SIANTAR
Kabupaten : SIMPANG TUNJUNG";

        $parsed = $this->service->parse($text);

        $this->assertEquals('JL. PENDIDIKAN NO. 12', $parsed['alamat']);
        $this->assertEquals('RT 001/RW 005', $parsed['rt_rw']);
        $this->assertEquals('SIANTAR MARTOBA', $parsed['kel_desa']);
        $this->assertEquals('KEC. SIANTAR', $parsed['kec']);
    }

    /**
     * Address with long street name.
     */
    public function test_address_with_long_street_name(): void
    {
        $text = "Alamat : JL. PROF. DR. SOEBRONO SECTOR TWELVE NO. 123
RT/RW : 005/010
Kel/Desa : KELURAHAN BERSAMA";

        $parsed = $this->service->parse($text);

        $this->assertStringContainsString('PROF', $parsed['alamat']);
        $this->assertEquals('RT 005/RW 010', $parsed['rt_rw']);
    }

    /**
     * Address without RT/RW component.
     */
    public function test_address_without_rt_rw(): void
    {
        $text = "Alamat : JL. TANAH DATAR NO. 1
Kel/Desa : PADANG
Kecamatan : PADANG UTARA";

        $parsed = $this->service->parse($text);

        $this->assertNotEmpty($parsed['alamat']);
        $this->assertEquals('', $parsed['rt_rw']);
    }

    /**
     * Address with hamlet (Dusun) instead of street.
     */
    public function test_address_with_dusun(): void
    {
        $text = "Alamat : DUSUN MEKAR JAYA NO. 5
RT/RW : 002/003
Kel/Desa : DESA JAYA
Kecamatan : BINTAN";

        $parsed = $this->service->parse($text);

        $this->assertStringContainsString('DUSUN', $parsed['alamat']);
        $this->assertEquals('DUSA JAYA', $parsed['kel_desa']);
    }

    // ========================================================================
    // SPECIAL CHARACTERS IN NAMES
    // ========================================================================

    /**
     * Name with apostrophe.
     */
    public function test_name_with_apostrophe(): void
    {
        $text = "Nama : O'REILLY WILLIAM'S";
        $parsed = $this->service->parse($text);

        $this->assertStringContainsString("'", $parsed['nama_lengkap']);
    }

    /**
     * Name with hyphen/dash.
     */
    public function test_name_with_hyphen(): void
    {
        $text = "Nama : BUDI SANTOSO-HERMANSYAH";
        $parsed = $this->service->parse($text);

        $this->assertStringContainsString('-', $parsed['nama_lengkap']);
    }

    /**
     * Name with period (sapaan).
     */
    public function test_name_with_period(): void
    {
        $text = "Nama : DR. H. AHMAD DAHLAN";
        $parsed = $this->service->parse($text);

        $this->assertStringContainsString('DR', $parsed['nama_lengkap']);
    }

    /**
     * Name with comma (multiple names).
     */
    public function test_name_with_comma(): void
    {
        $text = "Nama : HALIM, S.IP";
        $parsed = $this->service->parse($text);

        // Should handle comma in name
        $this->assertNotEmpty($parsed['nama_lengkap']);
    }

    // ========================================================================
    // RT/RW FORMAT VARIATIONS
    // ========================================================================

    /**
     * RT/RW format: RT 001/RW 005
     */
    public function test_rt_rw_format_with_space(): void
    {
        $text = "RT/RW : 001/005";
        $parsed = $this->service->parse($text);

        $this->assertEquals('RT 001/RW 005', $parsed['rt_rw']);
    }

    /**
     * RT/RW format: RT 001 / RW 005
     */
    public function test_rt_rw_format_with_spaces_around_slash(): void
    {
        $text = "RT/RW : 001 / 005";
        $parsed = $this->service->parse($text);

        $this->assertEquals('RT 001/RW 005', $parsed['rt_rw']);
    }

    /**
     * RT/RW format: RT.001/RW.005
     */
    public function test_rt_rw_format_with_dot(): void
    {
        $text = "RT.001/RW.005";
        $parsed = $this->service->parse($text);

        $this->assertEquals('RT 001/RW 005', $parsed['rt_rw']);
    }

    /**
     * RT/RW format: RT:001 RW:005
     */
    public function test_rt_rw_format_with_colon(): void
    {
        $text = "RT/RW : RT:001 RW:005";
        $parsed = $this->service->parse($text);

        $this->assertEquals('RT 001/RW 005', $parsed['rt_rw']);
    }

    /**
     * RT/RW format: single digit numbers.
     */
    public function test_rt_rw_single_digit(): void
    {
        $text = "RT/RW : 1/2";
        $parsed = $this->service->parse($text);

        $this->assertEquals('RT 001/RW 002', $parsed['rt_rw']);
    }

    /**
     * RT/RW format: two digit numbers.
     */
    public function test_rt_rw_two_digit(): void
    {
        $text = "RT/RW : 12/34";
        $parsed = $this->service->parse($text);

        $this->assertEquals('RT 012/RW 034', $parsed['rt_rw']);
    }

    // ========================================================================
    // EMPTY / NULL FIELD HANDLING
    // ========================================================================

    /**
     * Empty string input.
     */
    public function test_empty_string_input(): void
    {
        $parsed = $this->service->parse('');

        $this->assertEquals('', $parsed['nik']);
        $this->assertEquals('', $parsed['nama_lengkap']);
        $this->assertEquals(0.0, $parsed['confidence']);
    }

    /**
     * Only whitespace input.
     */
    public function test_whitespace_only_input(): void
    {
        $parsed = $this->service->parse("   \n\n   \t   ");

        $this->assertEquals('', $parsed['nik']);
        $this->assertEquals(0.0, $parsed['confidence']);
    }

    /**
     * Input with only labels (no values).
     */
    public function test_labels_without_values(): void
    {
        $text = "NIK :
Nama :
Tempat Lahir :
Tanggal Lahir :";

        $parsed = $this->service->parse($text);

        $this->assertEquals('', $parsed['nik']);
        $this->assertEquals('', $parsed['nama_lengkap']);
        $this->assertLessThan(0.5, $parsed['confidence']);
    }

    /**
     * Missing optional fields.
     */
    public function test_missing_optional_fields(): void
    {
        $text = "NIK : 1201011708900001
Nama : BUDI SANTOSO";

        $parsed = $this->service->parse($text);

        // Required fields still extracted
        $this->assertEquals('1201011708900001', $parsed['nik']);
        $this->assertEquals('BUDI SANTOSO', $parsed['nama_lengkap']);

        // Optional fields empty
        $this->assertEquals('', $parsed['gol_darah']);
        $this->assertEquals('', $parsed['pekerjaan']);
        $this->assertEquals('', $parsed['berlaku_hingga']);
    }

    /**
     * Null value in field.
     */
    public function test_null_value_handling(): void
    {
        $text = "NIK : 1201011708900001\nNama : null\nAlamat :";

        $parsed = $this->service->parse($text);

        $this->assertEquals('1201011708900001', $parsed['nik']);
        $this->assertNotEquals('null', $parsed['nama_lengkap']);
    }

    // ========================================================================
    // INDONESIAN CHARACTER ENCODING
    // ========================================================================

    /**
     * Name with accented characters (é, è, ê).
     */
    public function test_name_with_accented_characters(): void
    {
        $text = "Nama : THÉODORE RENAUD";
        $parsed = $this->service->parse($text);

        // Should handle accented characters
        $this->assertNotEmpty($parsed['nama_lengkap']);
    }

    /**
     * Name with Indonesian-specific characters.
     */
    public function test_name_with_indonesian_chars(): void
    {
        $text = "Nama : WIJAYANTO KUSUMA";
        $parsed = $this->service->parse($text);

        $this->assertEquals('WIJAYANTO KUSUMA', $parsed['nama_lengkap']);
    }

    /**
     * Place with Indonesian diacritics.
     */
    public function test_place_with_diacritics(): void
    {
        $text = "Tempat Lahir : TJURU";

        $parsed = $this->service->parse($text);

        $this->assertEquals('TJURU', $parsed['tempat_lahir']);
    }

    /**
     * Religion name with special characters.
     */
    public function test_religion_with_special_chars(): void
    {
        $text = "Agama : KONGHUCU";

        $parsed = $this->service->parse($text);

        $this->assertEquals('KONGHUCU', $parsed['agama']);
    }

    /**
     * Place with apostrophe (Cianjur's, etc.).
     */
    public function test_place_with_apostrophe(): void
    {
        $text = "Tempat Lahir : SITUJUH LIMA";

        $parsed = $this->service->parse($text);

        $this->assertNotEmpty($parsed['tempat_lahir']);
    }

    // ========================================================================
    // ADDITIONAL EDGE CASES
    // ========================================================================

    /**
     * Very long NIK extraction.
     */
    public function test_very_long_text_input(): void
    {
        $longText = str_repeat("NIK : 1201011708900001\nNama : BUDI SANTOSO\n", 100);
        $parsed = $this->service->parse($longText);

        // Should handle without crashing
        $this->assertEquals('1201011708900001', $parsed['nik']);
    }

    /**
     * Multiple NIK in text - should get first valid.
     */
    public function test_multiple_nik_in_text(): void
    {
        $text = "NIK : 1201011708900001
Nama : BUDI
OTHER NIK : 3175000000000002
Another : 5101010101010101";

        $parsed = $this->service->parse($text);

        // Should get the first valid NIK
        $this->assertEquals('1201011708900001', $parsed['nik']);
    }

    /**
     * NIK with surrounding text.
     */
    public function test_nik_with_surrounding_text(): void
    {
        $text = "Data NIK: 1201011708900001 adalah nomor KTP";
        $parsed = $this->service->parse($text);

        $this->assertEquals('1201011708900001', $parsed['nik']);
    }

    /**
     * OCR noise - very long lines.
     */
    public function test_very_long_single_line(): void
    {
        $longLine = "Nama : " . str_repeat('X', 500);
        $parsed = $this->service->parse($longLine);

        // Should handle without crashing, truncated to max 60 chars
        $this->assertNotEmpty($parsed['nama_lengkap']);
    }

    /**
     * Gender variations.
     */
    public function test_gender_variations(): void
    {
        $cases = [
            ['text' => 'Jenis Kelamin : LAKI-LAKI', 'expected' => 'LAKI-LAKI'],
            ['text' => 'Jenis Kelamin : PEREMPUAN', 'expected' => 'PEREMPUAN'],
            ['text' => 'Jenis Kelamin : LAKI LAKI', 'expected' => 'LAKI-LAKI'],
            ['text' => 'Jenis Kelamin : LAKI-LAKI', 'expected' => 'LAKI-LAKI'],
        ];

        foreach ($cases as $case) {
            $parsed = $this->service->parse($case['text']);
            $this->assertEquals($case['expected'], $parsed['jenis_kelamin'], "Failed: {$case['text']}");
        }
    }

    /**
     * Marital status variations.
     */
    public function test_marital_status_variations(): void
    {
        $cases = [
            ['text' => 'Status Perkawinan : BELUM KAWIN', 'expected' => 'BELUM KAWIN'],
            ['text' => 'Status Perkawinan : KAWIN', 'expected' => 'KAWIN'],
            ['text' => 'Status Perkawinan : CERAI HIDUP', 'expected' => 'CERAI HIDUP'],
            ['text' => 'Status Perkawinan : CERAI MATI', 'expected' => 'CERAI MATI'],
        ];

        foreach ($cases as $case) {
            $parsed = $this->service->parse($case['text']);
            $this->assertEquals($case['expected'], $parsed['status_perkawinan'], "Failed: {$case['text']}");
        }
    }

    /**
     * Religion variations.
     */
    public function test_religion_variations(): void
    {
        $religions = ['ISLAM', 'KRISTEN', 'KATOLIK', 'HINDU', 'BUDDHA', 'KHONGHUCU'];

        foreach ($religions as $religion) {
            $parsed = $this->service->parse("Agama : {$religion}");
            $this->assertEquals($religion, $parsed['agama'], "Failed: {$religion}");
        }
    }

    /**
     * Blood type variations.
     */
    public function test_blood_type_variations(): void
    {
        $cases = [
            ['text' => 'Gol. Darah : A', 'expected' => 'A'],
            ['text' => 'Gol. Darah : B+', 'expected' => 'B+'],
            ['text' => 'Gol. Darah : AB-', 'expected' => 'AB-'],
            ['text' => 'Gol. Darah : O', 'expected' => 'O'],
        ];

        foreach ($cases as $case) {
            $parsed = $this->service->parse($case['text']);
            $this->assertEquals($case['expected'], $parsed['gol_darah'], "Failed: {$case['text']}");
        }
    }

    /**
     * Citizenship default to WNI.
     */
    public function test_citizenship_defaults_to_wni(): void
    {
        $text = "NIK : 1201011708900001\nNama : BUDI";

        $parsed = $this->service->parse($text);

        // Default WNI when not specified
        $this->assertEquals('WNI', $parsed['kewarganegaraan']);
    }

    /**
     * Address component order variations.
     */
    public function test_address_component_order(): void
    {
        $text = "Alamat : JL. MUNCAR NO. 8
Kecamatan : MUNCAR
Kel/Desa : MUNCAR
Kabupaten : BANYUWANGI
Provinsi : JAWA TIMUR";

        $parsed = $this->service->parse($text);

        $this->assertEquals('JAWA TIMUR', $parsed['provinsi']);
        $this->assertEquals('BANYUWANGI', $parsed['kab_kota']);
    }

    /**
     * Complete KTP text.
     */
    public function test_complete_ktp_text(): void
    {
        $completeText = <<<'KTPTEXT'
NIK : 1201011708900001
Nama : BAMBANG SUPRIYANTO
Tempat Lahir : SIANTAR
Tanggal Lahir : 17-08-1990
Jenis Kelamin : LAKI-LAKI
Gol. Darah : B
Alamat : JL. PENDIDIKAN NO. 12
RT/RW : 001/005
Kel/Desa : SIANTAR MARToba
Kecamatan : SIANTAR
Kabupaten : SIMPANG TUNJUNG
Provinsi : SUMATERA UTARA
Agama : ISLAM
Status Perkawinan : KAWIN
Pekerjaan : PNS
Kewarganegaraan : WNI
Berlaku Hingga : SEUMUR HIDUP
KTPTEXT;

        $parsed = $this->service->parse($completeText);

        $this->assertEquals('1201011708900001', $parsed['nik']);
        $this->assertEquals('BAMBANG SUPRIYANTO', $parsed['nama_lengkap']);
        $this->assertEquals('SIANTAR', $parsed['tempat_lahir']);
        $this->assertEquals('17-08-1990', $parsed['tanggal_lahir']);
        $this->assertEquals('LAKI-LAKI', $parsed['jenis_kelamin']);
        $this->assertEquals('B', $parsed['gol_darah']);
        $this->assertEquals('ISLAM', $parsed['agama']);
        $this->assertEquals('KAWIN', $parsed['status_perkawinan']);
        $this->assertEquals('PNS', $parsed['pekerjaan']);
        $this->assertEquals('WNI', $parsed['kewarganegaraan']);
        $this->assertGreaterThan(0.9, $parsed['confidence']);
    }
}