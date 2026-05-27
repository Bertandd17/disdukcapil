<?php

namespace Tests\Unit\Services;

use App\Services\KtpOcrExtractionService;
use Tests\TestCase;

class KtpOcrExtractionServiceTest extends TestCase
{
    private KtpOcrExtractionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new KtpOcrExtractionService();
    }

    public function test_nik_extraction_with_label(): void
    {
        $text = "NIK : 1201011708900001";

        $result = $this->service->extract($text);

        $this->assertEquals('1201011708900001', $result['nik']);
        $this->assertGreaterThan(0.0, $result['field_confidence']['nik']);
    }

    public function test_nik_extraction_raw_16_digits(): void
    {
        $text = "SOME TEXT 1271054101950001 MORE TEXT";

        $result = $this->service->extract($text);

        $this->assertEquals('1271054101950001', $result['nik']);
    }

    public function test_nik_extraction_with_misread_digits_o_to_zero(): void
    {
        $text = "NIK : 12010117089OOO1";

        $result = $this->service->extract($text);

        $this->assertEquals('1201011708900001', $result['nik']);
    }

    public function test_nik_extraction_with_misread_digits_i_to_one(): void
    {
        $text = "NIK : 12010117O89O00I";

        $result = $this->service->extract($text);

        $this->assertEquals('1201011708900011', $result['nik']);
    }

    public function test_nama_lengkap_extraction_with_label(): void
    {
        $text = "Nama : BAMBANG SUPRIYANTO";

        $result = $this->service->extract($text);

        $this->assertEquals('BAMBANG SUPRIYANTO', $result['nama_lengkap']);
    }

    public function test_nama_lengkap_extraction_all_caps_fallback(): void
    {
        $text = "SOME LABEL\nBUDI SANTOSO\nJL MERDEKA";

        $result = $this->service->extract($text);

        $this->assertEquals('BUDI SANTOSO', $result['nama_lengkap']);
    }

    public function test_tempat_lahir_extraction(): void
    {
        $text = "Tempat Lahir : SIANTAR";

        $result = $this->service->extract($text);

        $this->assertEquals('SIANTAR', $result['tempat_lahir']);
    }

    public function test_tanggal_lahir_extraction_dash_format(): void
    {
        $text = "Tanggal Lahir : 17-08-1990";

        $result = $this->service->extract($text);

        $this->assertEquals('17-08-1990', $result['tanggal_lahir']);
    }

    public function test_tanggal_lahir_extraction_slash_format(): void
    {
        $text = "Tanggal Lahir : 17/08/1990";

        $result = $this->service->extract($text);

        $this->assertEquals('17/08/1990', $result['tanggal_lahir']);
    }

    public function test_tanggal_lahir_extraction_single_digit_day_month(): void
    {
        $text = "Tgl Lahir : 5-3-1990";

        $result = $this->service->extract($text);

        $this->assertEquals('05-03-1990', $result['tanggal_lahir']);
    }

    public function test_alamat_extraction(): void
    {
        $text = "Alamat : JL. PENDIDIKAN NO. 12";

        $result = $this->service->extract($text);

        $this->assertEquals('JL. PENDIDIKAN NO. 12', $result['alamat']);
    }

    public function test_alamat_extraction_multiline(): void
    {
        $text = "Alamat : JL. MERDEKA NO. 5
RT/RW : 001/005
Kel/Desa : SIANTAR MARToba
Kecamatan : SIANTAR";

        $result = $this->service->extract($text);

        $this->assertStringContainsString('JL. MERDEKA NO. 5', $result['alamat']);
        $this->assertStringContainsString('RT 001/RW 005', $result['rt_rw']);
        $this->assertStringContainsString('SIANTAR MARToba', $result['kel_desa']);
        $this->assertStringContainsString('Kec. SIANTAR', $result['kecamatan']);
    }

    public function test_rt_rw_extraction_standard_format(): void
    {
        $text = "RT/RW : 001/002";

        $result = $this->service->extract($text);

        $this->assertEquals('RT 001/RW 002', $result['rt_rw']);
    }

    public function test_rt_rw_extraction_no_leading_zeros(): void
    {
        $text = "RT/RW : 1/5";

        $result = $this->service->extract($text);

        $this->assertEquals('RT 001/RW 005', $result['rt_rw']);
    }

    public function test_kel_desa_extraction(): void
    {
        $text = "Kel/Desa : PASAR BARU";

        $result = $this->service->extract($text);

        $this->assertEquals('PASAR BARU', $result['kel_desa']);
    }

    public function test_kecamatan_extraction(): void
    {
        $text = "Kecamatan : MENTENG";

        $result = $this->service->extract($text);

        $this->assertEquals('Kec. MENTENG', $result['kecamatan']);
    }

    public function test_agama_extraction_all_variants(): void
    {
        $religions = ['ISLAM', 'KRISTEN', 'KATOLIK', 'HINDU', 'BUDDHA', 'KHONGHUCU'];

        foreach ($religions as $religion) {
            $text = "Agama : {$religion}";
            $result = $this->service->extract($text);
            $this->assertEquals($religion, $result['agama'], "Failed for religion: {$religion}");
        }
    }

    public function test_status_perkawinan_belum_kawin(): void
    {
        $text = "Status Perkawinan : BELUM KAWIN";

        $result = $this->service->extract($text);

        $this->assertEquals('BELUM KAWIN', $result['status_perkawinan']);
    }

    public function test_status_perkawinan_kawin(): void
    {
        $text = "Status Perkawinan : KAWIN";

        $result = $this->service->extract($text);

        $this->assertEquals('KAWIN', $result['status_perkawinan']);
    }

    public function test_status_perkawinan_cerai_hidup(): void
    {
        $text = "Status Perkawinan : CERAI HIDUP";

        $result = $this->service->extract($text);

        $this->assertEquals('CERAI HIDUP', $result['status_perkawinan']);
    }

    public function test_status_perkawinan_cerai_mati(): void
    {
        $text = "Status Perkawinan : CERAI MATI";

        $result = $this->service->extract($text);

        $this->assertEquals('CERAI MATI', $result['status_perkawinan']);
    }

    public function test_nik_checksum_validation_valid(): void
    {
        $validNik = '3171034102930005';
        $text = "NIK : {$validNik}";

        $result = $this->service->extract($text);

        $this->assertEquals($validNik, $result['nik']);
        $this->assertEquals(1.0, $result['field_confidence']['nik']);
    }

    public function test_nik_checksum_validation_invalid(): void
    {
        $invalidNik = '3171034102930001';
        $text = "NIK : {$invalidNik}";

        $result = $this->service->extract($text);

        $this->assertEquals($invalidNik, $result['nik']);
        $this->assertLessThan(1.0, $result['field_confidence']['nik']);
    }

    public function test_nik_invalid_province_code(): void
    {
        $text = "NIK : 9901011708900001";

        $result = $this->service->extract($text);

        $this->assertEquals('9901011708900001', $result['nik']);
        $this->assertLessThan(1.0, $result['field_confidence']['nik']);
    }

    public function test_confidence_scoring_high(): void
    {
        $text = "NIK : 1201011708900001
Nama : BAMBANG SUPRIYANTO
Tempat Lahir : SIANTAR
Tanggal Lahir : 17-08-1990
Alamat : JL. PENDIDIKAN NO. 12
RT/RW : 001/005
Kel/Desa : SIANTAR
Kecamatan : SIANTAR
Agama : ISLAM
Status Perkawinan : KAWIN";

        $result = $this->service->extract($text);

        $this->assertGreaterThan(0.7, $result['confidence']);
        $this->assertFalse($result['needs_manual_review']);
        $this->assertIsArray($result['field_confidence']);
    }

    public function test_confidence_scoring_low_triggers_review(): void
    {
        $text = "SOME RANDOM TEXT";

        $result = $this->service->extract($text);

        $this->assertLessThan(0.7, $result['confidence']);
        $this->assertTrue($result['needs_manual_review']);
    }

    public function test_empty_text_returns_empty_values(): void
    {
        $result = $this->service->extract('');

        $this->assertEquals('', $result['nik']);
        $this->assertEquals('', $result['nama_lengkap']);
        $this->assertEquals('', $result['tempat_lahir']);
        $this->assertEquals('', $result['tanggal_lahir']);
        $this->assertEquals('', $result['alamat']);
        $this->assertEquals(0.0, $result['confidence']);
        $this->assertTrue($result['needs_manual_review']);
    }

    public function test_complete_ktp_text(): void
    {
        $text = "PROVINSI SUMATERA UTARA
KOTA Pematangsiantar
NIK : 1201011708900001
Nama : BAMBANG SUPRIYANTO
Tempat Lahir : SIANTAR
Tanggal Lahir : 17-08-1990
Jenis Kelamin : LAKI-LAKI
Alamat : JL. PENDIDIKAN NO. 12
RT/RW : 001/005
Kel/Desa : SIANTAR MARToba
Kecamatan : SIANTAR
Kabupaten : SIMPANG TUNJUNG
Provinsi : SUMATERA UTARA
Agama : ISLAM
Status Perkawinan : KAWIN
Pekerjaan : PNS
Kewarganegaraan : WNI";

        $result = $this->service->extract($text);

        $this->assertEquals('1201011708900001', $result['nik']);
        $this->assertEquals('BAMBANG SUPRIYANTO', $result['nama_lengkap']);
        $this->assertEquals('SIANTAR', $result['tempat_lahir']);
        $this->assertEquals('17-08-1990', $result['tanggal_lahir']);
        $this->assertStringContainsString('JL. PENDIDIKAN NO. 12', $result['alamat']);
        $this->assertEquals('RT 001/RW 005', $result['rt_rw']);
        $this->assertEquals('SIANTAR MARToba', $result['kel_desa']);
        $this->assertEquals('Kec. SIANTAR', $result['kecamatan']);
        $this->assertEquals('ISLAM', $result['agama']);
        $this->assertEquals('KAWIN', $result['status_perkawinan']);
        $this->assertGreaterThan(0.5, $result['confidence']);
        $this->assertIsArray($result['field_confidence']);
    }

    public function test_oversized_nik_rejected(): void
    {
        $text = "NIK : 12010117089000011234";

        $result = $this->service->extract($text);

        $this->assertEquals('', $result['nik']);
    }

    public function test_numeric_label_not_extracted_as_nama(): void
    {
        $text = "NIK : 1201011708900001\n500\nBUDI SANTOSO";

        $result = $this->service->extract($text);

        $this->assertEquals('BUDI SANTOSO', $result['nama_lengkap']);
    }

    public function test_confidence_per_field(): void
    {
        $text = "NIK : 1201011708900001
Nama : BUDI";

        $result = $this->service->extract($text);

        $this->assertArrayHasKey('nik', $result['field_confidence']);
        $this->assertArrayHasKey('nama_lengkap', $result['field_confidence']);
        $this->assertArrayHasKey('tempat_lahir', $result['field_confidence']);
        $this->assertArrayHasKey('tanggal_lahir', $result['field_confidence']);
        $this->assertArrayHasKey('alamat', $result['field_confidence']);
        $this->assertArrayHasKey('rt_rw', $result['field_confidence']);
        $this->assertArrayHasKey('kel_desa', $result['field_confidence']);
        $this->assertArrayHasKey('kecamatan', $result['field_confidence']);
        $this->assertArrayHasKey('agama', $result['field_confidence']);
        $this->assertArrayHasKey('status_perkawinan', $result['field_confidence']);
    }
}