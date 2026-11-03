<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SaasService;

class SaasServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name'           => '80mm Thermal Receipt Printer (USB + LAN)',
                'name_np'        => '८० एमएम थर्मल बिल प्रिन्टर (Thermal Receipt Printer)',
                'code'           => 'SRV-THERMAL-PRINTER',
                'category'       => 'hardware',
                'default_price'  => 12500.00,
                'billing_basis'  => 'one_time',
                'unit_name'      => 'थान (Unit)',
                'description'    => 'High-speed 80mm thermal receipt printer with auto-cutter for fee counter, cafeteria, and library billing. Plug & play with NepalSchoolHub.',
                'specifications' => 'Print speed 260mm/s, Auto-cutter, USB + Ethernet LAN + Cash drawer kick-out port, 80mm paper roll width.',
                'is_featured'    => true,
            ],
            [
                'name'           => 'CCTV Camera Setup (4-Channel Full HD Kit & Installation)',
                'name_np'        => 'सीसीटीभी क्यामेरा ४ च्यानल सेटअप (CCTV Camera System)',
                'code'           => 'SRV-CCTV-4CH',
                'category'       => 'hardware',
                'default_price'  => 38000.00,
                'billing_basis'  => 'one_time',
                'unit_name'      => 'सेट (Set)',
                'description'    => 'Complete 4-camera HD surveillance package with DVR/NVR, hard disk storage, cabling, night vision, and mobile live viewing app configuration.',
                'specifications' => '4x 2MP/5MP Night Vision Dome/Bullet Cameras, 4-Channel DVR/NVR, 1TB Surveillance HDD, Video Balun, Power Supply, Mobile App Live Remote Viewing.',
                'is_featured'    => true,
            ],
            [
                'name'           => 'Full Computer Setup (Desktop PC + Monitor + UPS)',
                'name_np'        => 'कम्प्युटर पूर्ण सेटअप (Computer Full Setup)',
                'code'           => 'SRV-PC-FULL-SETUP',
                'category'       => 'hardware',
                'default_price'  => 52000.00,
                'billing_basis'  => 'one_time',
                'unit_name'      => 'सेट (Set)',
                'description'    => 'Complete school office administration computer setup. High performance for accounts, report card generation, and NepalSchoolHub administration.',
                'specifications' => 'Intel Core i5 (11th/12th Gen), 16GB DDR4 RAM, 512GB NVMe M.2 SSD, 21.5" IPS Full HD Monitor, Logitech Keyboard & Mouse, 600VA Offline UPS.',
                'is_featured'    => true,
            ],
            [
                'name'           => 'Biometric Attendance Device (Fingerprint + RFID + WiFi)',
                'name_np'        => 'बायोमेट्रिक हाजिरी मेसिन (Biometric Attendance Machine)',
                'code'           => 'SRV-BIOMETRIC-DEVICE',
                'category'       => 'hardware',
                'default_price'  => 18500.00,
                'billing_basis'  => 'one_time',
                'unit_name'      => 'थान (Unit)',
                'description'    => 'Cloud-synced biometric device for staff and student real-time attendance with automated SMS trigger upon check-in/check-out.',
                'specifications' => 'Optical Fingerprint Sensor, 125kHz RFID card reader, TCP/IP, WiFi, USB drive download, Battery backup support, 3,000 users capacity.',
                'is_featured'    => true,
            ],
            [
                'name'           => 'NepalSchoolHub Initial Setup & Implementation Fee',
                'name_np'        => 'पहिलो वर्षको प्रारम्भिक स्थापना तथा प्रणाली सञ्चालन शुल्क',
                'code'           => 'SRV-INITIAL-SETUP',
                'category'       => 'setup',
                'default_price'  => 15000.00,
                'billing_basis'  => 'one_time',
                'unit_name'      => 'पहिलो वर्षका लागि एकपटक',
                'description'    => 'Initial system onboarding, database provisioning, custom subdomain configuration, school branding, fee structure setup, and staff training.',
                'specifications' => 'Dedicated subdomain, master data migration (students, teachers, fees), admin and teacher orientation, branded report card template design.',
                'is_featured'    => true,
            ],
            [
                'name'           => 'NepalSchoolHub Annual Software Renewal & Technical Support',
                'name_np'        => 'वार्षिक नवीकरण तथा प्राविधिक सहयोग शुल्क',
                'code'           => 'SRV-ANNUAL-RENEWAL',
                'category'       => 'service',
                'default_price'  => 10000.00,
                'billing_basis'  => 'annual',
                'unit_name'      => 'दोस्रो वर्षदेखि प्रतिवर्ष',
                'description'    => 'Annual software license renewal, continuous cloud server hosting, automated daily backups, version updates, bug fixes, and 6-days-a-week phone/remote technical support.',
                'specifications' => 'Cloud server uptime guarantee 99.9%, daily encrypted backups, unlimited students/teachers, feature upgrades, technical support 10am-5pm Sun-Fri.',
                'is_featured'    => true,
            ],
            [
                'name'           => 'PVC ID Card Printer (Direct-to-Card Double-Sided)',
                'name_np'        => 'पीभीसी आईडी कार्ड प्रिन्टर (Smart PVC ID Card Printer)',
                'code'           => 'SRV-ID-CARD-PRINTER',
                'category'       => 'hardware',
                'default_price'  => 68000.00,
                'billing_basis'  => 'one_time',
                'unit_name'      => 'थान (Unit)',
                'description'    => 'High-definition color PVC card printer for instant on-campus printing of student, teacher, and staff identity cards with QR/barcodes.',
                'specifications' => 'Edge-to-edge full color dye-sublimation printing, 300 DPI, dual-sided printing option, includes ribbon and 100 blank PVC cards.',
                'is_featured'    => false,
            ],
            [
                'name'           => 'Bulk SMS Gateway Package (10,000 SMS)',
                'name_np'        => 'बल्क एसएमएस प्याकेज - १०,००० एसएमएस (Bulk SMS Gateway)',
                'code'           => 'SRV-SMS-10K',
                'category'       => 'service',
                'default_price'  => 14000.00,
                'billing_basis'  => 'per_unit',
                'unit_name'      => 'प्याकेज (Package)',
                'description'    => 'Official telecommunication gateway SMS credit pack with custom sender ID approval for attendance alerts, exam results, fees notices, and emergency broadcasts.',
                'specifications' => 'NTC and Ncell delivery, 10,000 SMS credits, 1-year validity, instant delivery API integration with NepalSchoolHub portal.',
                'is_featured'    => false,
            ],
        ];

        foreach ($services as $srv) {
            SaasService::updateOrCreate(
                ['code' => $srv['code']],
                $srv
            );
        }
    }
}

