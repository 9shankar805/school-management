<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\School;
use App\Models\SaasService;
use App\Models\SaasAgreement;
use App\Models\SaasInvoice;
use App\Models\SystemImprovement;
use App\Models\Feedback;
use Spatie\Permission\Models\Role;

class SaasBillingAndAgreementTest extends TestCase
{
    use RefreshDatabase;

    protected $superAdmin;
    protected $school;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'super-admin']);
        Role::create(['name' => 'student']);
        Role::create(['name' => 'teacher']);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super-admin');

        // Create sample school
        $this->school = School::create([
            'name'        => 'Happy Land English Boarding School Siraha',
            'slug'        => 'happyland',
            'email'       => 'happyland@school.np',
            'phone'       => '033-520111',
            'address'     => 'Siraha-01',
            'city'        => 'Siraha',
            'district'    => 'Siraha',
            'province'    => 'Madhesh',
            'status'      => 'active',
            'iemis_code'  => '602666398',
            'contact_person' => 'Principal Representative',
        ]);
    }

    public function test_super_admin_can_access_services_catalog_and_create_hardware_service()
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('super.services.index'));

        $response->assertStatus(200);
        $response->assertSee('Services &amp; Hardware Catalog', false);

        // Create Thermal Printer service
        $postResponse = $this->actingAs($this->superAdmin)
            ->post(route('super.services.store'), [
                'name'          => '80mm Thermal Receipt Printer',
                'name_np'       => '८० एमएम थर्मल बिल प्रिन्टर',
                'category'      => 'hardware',
                'default_price' => 12500,
                'billing_basis' => 'one_time',
                'unit_name'     => 'थान (Unit)',
                'description'   => 'High speed thermal printer for fee receipts',
                'is_active'     => 1,
            ]);

        $postResponse->assertRedirect(route('super.services.index'));

        $this->assertDatabaseHas('saas_services', [
            'name'          => '80mm Thermal Receipt Printer',
            'category'      => 'hardware',
            'default_price' => 12500,
        ]);
    }

    public function test_super_admin_can_create_and_view_official_agreement_paper()
    {
        // Add a hardware service first
        $thermalPrinter = SaasService::create([
            'name'          => 'Thermal Printer 80mm',
            'name_np'       => 'थर्मल बिल प्रिन्टर',
            'code'          => 'SRV-TEST-TP',
            'category'      => 'hardware',
            'default_price' => 12500,
            'billing_basis' => 'one_time',
            'unit_name'     => 'थान',
        ]);

        $cctv = SaasService::create([
            'name'          => 'CCTV Camera 4CH Setup',
            'name_np'       => 'सीसीटीभी क्यामेरा ४ च्यानल',
            'code'          => 'SRV-TEST-CCTV',
            'category'      => 'hardware',
            'default_price' => 38000,
            'billing_basis' => 'one_time',
            'unit_name'     => 'सेट',
        ]);

        $postData = [
            'agreement_number'        => 'NSH-AGR-2083-TEST',
            'school_id'               => $this->school->id,
            'status'                  => 'active',
            'agreement_date_bs'       => '२०८३ साल बैशाख १५ गते',
            'duration_years'          => 1,
            'effective_date'          => '2026-04-28',
            'expiry_date'             => '2027-04-28',
            'provider_name'           => 'टेक प्रोकोड प्रा. लि. (नेपालस्कुलहब)',
            'provider_reg_no'         => '३९१५१२/८२/८३',
            'provider_pan_vat'        => '६२३६०८२७२',
            'provider_address'        => 'सिरहा / काठमाडौँ, नेपाल',
            'provider_phone'          => '+९७७ ९८०५९१६५९८',
            'provider_email'          => 'support@techprocod.com.np',
            'provider_website'        => 'www.techprocod.com.np',
            'school_name'             => 'ह्यापी ल्याण्ड इङ्ग्लिस बोर्डिङ स्कुल सिरहा प्रा. लि.',
            'school_address'          => 'सिरहा नगरपालिका–०१, सिरहा, मधेश प्रदेश, नेपाल',
            'school_reg_no'           => '११४३८३/०७०/०७१',
            'school_pan_no'           => '६०२६६६३९८',
            'school_representative'   => 'अधिकृत प्रतिनिधि',
            'school_designation'      => 'प्रिन्सिपल',
            'setup_fee'               => 15000,
            'setup_fee_basis'         => 'पहिलो वर्षका लागि एकपटक',
            'renewal_fee'             => 10000,
            'renewal_fee_basis'       => 'दोस्रो वर्षदेखि प्रतिवर्ष',
            'software_inclusion_text' => 'समावेश (माथि उल्लिखित शुल्कभित्रै)',
            'witness_1_name'          => 'साक्षी १',
            'witness_2_name'          => 'साक्षी २',
            'items'                   => [
                [
                    'service_id' => $thermalPrinter->id,
                    'item_name'  => 'थर्मल बिल प्रिन्टर (Thermal Printer 80mm)',
                    'unit_price' => 12500,
                    'quantity'   => 1,
                    'basis'      => 'एकपटक',
                ],
                [
                    'service_id' => $cctv->id,
                    'item_name'  => 'सीसीटीभी क्यामेरा ४ च्यानल सेटअप (CCTV Camera System)',
                    'unit_price' => 38000,
                    'quantity'   => 1,
                    'basis'      => 'एकपटक',
                ],
            ],
        ];

        $response = $this->actingAs($this->superAdmin)
            ->post(route('super.agreements.store'), $postData);

        $agreement = SaasAgreement::where('agreement_number', 'NSH-AGR-2083-TEST')->first();
        $this->assertNotNull($agreement);
        $this->assertEquals(2, $agreement->items()->count());
        $this->assertEquals(15000 + 10000 + 12500 + 38000, $agreement->grand_total);

        // View agreement print page
        $printResponse = $this->actingAs($this->superAdmin)
            ->get(route('super.agreements.print', $agreement->id));

        $printResponse->assertStatus(200);
        $printResponse->assertSee('नेपाल स्कुल हब');
        $printResponse->assertSee('टेक प्रोकोड प्रा. लि.');
        $printResponse->assertSee('विद्यालय व्यवस्थापन प्रणालीसम्बन्धी सेवा सम्झौता');
        $printResponse->assertSee('१. उद्देश्य तथा सेवाको दायरा');
        $printResponse->assertSee('२. शुल्क तथा मूल्य संरचना');
        $printResponse->assertSee('१३. हस्ताक्षर तथा स्वीकृति');
        $printResponse->assertSee('थर्मल बिल प्रिन्टर');
        $printResponse->assertSee('सीसीटीभी क्यामेरा ४ च्यानल');
    }

    public function test_super_admin_can_create_invoice_and_record_payment()
    {
        $postData = [
            'invoice_number'  => 'NSH-INV-2083-TEST',
            'school_id'       => $this->school->id,
            'school_name'     => $this->school->name,
            'school_pan'      => '602666398',
            'school_address'  => 'Siraha-01',
            'invoice_date'    => '2026-05-01',
            'due_date'        => '2026-05-15',
            'discount_amount' => 1000,
            'tax_percent'     => 13,
            'items'           => [
                [
                    'title'        => 'NepalSchoolHub Initial Setup Fee',
                    'description'  => 'Setup, cloud server and staff training',
                    'quantity'     => 1,
                    'unit_price'   => 15000,
                ],
                [
                    'title'        => '80mm Thermal Receipt Printer',
                    'description'  => 'Auto-cutter USB+LAN',
                    'quantity'     => 2,
                    'unit_price'   => 12500,
                ],
            ],
        ];

        $response = $this->actingAs($this->superAdmin)
            ->post(route('super.billing.store'), $postData);

        $invoice = SaasInvoice::where('invoice_number', 'NSH-INV-2083-TEST')->first();
        $this->assertNotNull($invoice);

        // Subtotal = 15000 + 25000 = 40000
        // Discount = 1000 => After discount = 39000
        // Tax 13% of 39000 = 5070
        // Total = 44070
        $this->assertEquals(40000, $invoice->subtotal);
        $this->assertEquals(5070, $invoice->tax_amount);
        $this->assertEquals(44070, $invoice->total_amount);
        $this->assertEquals('unpaid', $invoice->status);

        // Record partial payment of 20000
        $payResponse = $this->actingAs($this->superAdmin)
            ->post(route('super.billing.payments.store', $invoice->id), [
                'amount'                => 20000,
                'payment_date'          => '2026-05-05',
                'payment_method'        => 'bank_transfer',
                'transaction_reference' => 'TXN-NABIL-9912',
                'bank_name'             => 'Nabil Bank',
            ]);

        $invoice->refresh();
        $this->assertEquals(20000, $invoice->paid_amount);
        $this->assertEquals(24070, $invoice->due_amount);
        $this->assertEquals('partial', $invoice->status);
    }

    public function test_super_admin_can_create_invoice_with_advance_and_print_exact_physical_pad()
    {
        $postData = [
            'invoice_number'  => 'TPC-2026-9999',
            'school_name'     => 'Janakpur Public High School',
            'school_address'  => 'Station Road, Janakpurdham, Nepal',
            'school_pan'      => '609887766',
            'invoice_date'    => '2026-05-10',
            'invoice_date_bs' => '२०८३/०१/२८',
            'discount_amount' => 2000,
            'advance_amount'  => 10000,
            'payment_method'  => 'cash',
            'tax_percent'     => 0,
            'items'           => [
                [
                    'title'      => 'Thermal Receipt Printer 80mm USB+LAN',
                    'quantity'   => 1,
                    'unit_price' => 8500,
                ],
                [
                    'title'      => 'CCTV Camera 4-Channel Kit',
                    'quantity'   => 1,
                    'unit_price' => 28000,
                ],
            ],
        ];

        $response = $this->actingAs($this->superAdmin)
            ->post(route('super.billing.store'), $postData);

        $invoice = SaasInvoice::where('invoice_number', 'TPC-2026-9999')->first();
        $this->assertNotNull($invoice);

        // Subtotal = 8500 + 28000 = 36500
        // Discount = 2000 => Total = 34500
        // Advance = 10000 => Due = 24500
        $this->assertEquals(36500, $invoice->subtotal);
        $this->assertEquals(2000, $invoice->discount_amount);
        $this->assertEquals(34500, $invoice->total_amount);
        $this->assertEquals(10000, $invoice->paid_amount);
        $this->assertEquals(24500, $invoice->due_amount);
        $this->assertEquals('partial', $invoice->status);
        $this->assertEquals(1, $invoice->payments()->count());

        // Check Print view renders the exact physical voucher layout
        $printResponse = $this->actingAs($this->superAdmin)
            ->get(route('super.billing.print', $invoice->id));

        $printResponse->assertStatus(200);
        $printResponse->assertSee('Techprocod Pvt. Ltd.');
        $printResponse->assertSee('टेक प्रोकोड प्रा.लि');
        $printResponse->assertSee('TPC-2026-9999');
        $printResponse->assertSee('Janakpur Public High School');
        $printResponse->assertSee('२०८३/०१/२८');
        $printResponse->assertSee('Sub Total');
        $printResponse->assertSee('Advance');
        $printResponse->assertSee('Discount');
        $printResponse->assertSee('Grand Total');
        $printResponse->assertSee('For: Techprocod Pvt. Ltd.');
        $printResponse->assertSee('Thirty Four Thousand Five Hundred Rupees Only');
    }

    public function test_super_admin_dashboard_renders_with_saas_metrics_and_agreements()
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(url('/home'));

        $response->assertStatus(200);
        $response->assertSee('Partner School Agreements, SaaS Billing &amp; Hardware Catalog', false);
        $response->assertSee('Service Agreement');
        $response->assertSee('SaaS Invoice');
        $response->assertSee('Hardware Setup');
    }

    public function test_super_admin_can_manage_saas_staff_and_assign_permissions()
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'saas-staff']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'manage saas billing']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'manage saas schools']);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('super.staff.index'));

        $response->assertStatus(200);
        $response->assertSee('SaaS Staff &amp; Team Management', false);

        // Create new SaaS Staff
        $postResponse = $this->actingAs($this->superAdmin)
            ->post(route('super.staff.store'), [
                'first_name'            => 'Bibek',
                'last_name'             => 'Yadav',
                'email'                 => 'bibek@techprocod.com.np',
                'phone'                 => '9801234567',
                'password'              => 'Secret123!',
                'password_confirmation' => 'Secret123!',
                'role'                  => 'saas-staff',
                'permissions'           => ['manage saas billing', 'manage saas schools'],
            ]);

        $postResponse->assertRedirect(route('super.staff.index'));

        $staffUser = User::where('email', 'bibek@techprocod.com.np')->first();
        $this->assertNotNull($staffUser);
        $this->assertNull($staffUser->school_id);
        $this->assertTrue($staffUser->hasRole('saas-staff'));
        $this->assertTrue($staffUser->hasPermissionTo('manage saas billing'));
        $this->assertTrue($staffUser->isSaasStaff());
    }

    public function test_super_admin_can_manage_school_plans_and_subscriptions()
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('super.subscriptions.index'));

        $response->assertStatus(200);
        $response->assertSee('School Plans &amp; Subscriptions', false);
        $response->assertSee($this->school->name);

        // Update plan to Growth with 12 months duration
        $updateResponse = $this->actingAs($this->superAdmin)
            ->patch(route('super.subscriptions.update', $this->school->id), [
                'plan'                => 'growth',
                'duration_months'     => 12,
                'max_students'        => 1200,
                'max_teachers'        => 60,
                'max_staff'           => 30,
                'ai_features_enabled' => 1,
            ]);

        $updateResponse->assertSessionHas('success');

        $this->school->refresh();
        $this->assertEquals('growth', $this->school->plan);
        $this->assertEquals(1200, $this->school->max_students);
        $this->assertTrue((bool)$this->school->ai_features_enabled);
        $this->assertNotNull($this->school->plan_expires_at);
    }

    public function test_super_admin_can_manage_system_improvements_and_toggle_done_remaining()
    {
        // 1. Visit improvements index
        $indexResponse = $this->actingAs($this->superAdmin)
            ->get(route('super.improvements.index'));

        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('System Improvements');
        $indexResponse->assertSee('प्रणाली सुधार तथा कार्य सूची');

        // 2. Create a new improvement item
        $createResponse = $this->actingAs($this->superAdmin)
            ->post(route('super.improvements.store'), [
                'title'       => 'Optimize Redis cache for multi-tenant fee structures',
                'description' => 'Cache repeated fee inquiries to reduce SQL latency on large schools.',
                'priority'    => 'high',
                'category'    => 'performance',
                'module'      => 'finance',
                'notes'       => 'Requires redis key prefixing by school_id',
            ]);

        $createResponse->assertRedirect(route('super.improvements.index'));

        $item = SystemImprovement::where('title', 'Optimize Redis cache for multi-tenant fee structures')->first();
        $this->assertNotNull($item);
        $this->assertEquals('remaining', $item->status);
        $this->assertEquals('high', $item->priority);
        $this->assertEquals('performance', $item->category);
        $this->assertNull($item->completed_at);

        // 3. Toggle to Done
        $toggleDoneResponse = $this->actingAs($this->superAdmin)
            ->patch(route('super.improvements.toggle', $item->id));

        $toggleDoneResponse->assertSessionHas('success');
        $item->refresh();
        $this->assertEquals('done', $item->status);
        $this->assertNotNull($item->completed_at);
        $this->assertEquals($this->superAdmin->id, $item->completed_by);

        // 4. Toggle back to Remaining
        $toggleRemainingResponse = $this->actingAs($this->superAdmin)
            ->patch(route('super.improvements.toggle', $item->id));

        $toggleRemainingResponse->assertSessionHas('success');
        $item->refresh();
        $this->assertEquals('remaining', $item->status);
        $this->assertNull($item->completed_at);
        $this->assertNull($item->completed_by);

        // 5. Update improvement item
        $updateResponse = $this->actingAs($this->superAdmin)
            ->put(route('super.improvements.update', $item->id), [
                'title'    => 'Optimize Redis cache - v2',
                'priority' => 'urgent',
                'category' => 'security',
                'status'   => 'remaining',
            ]);

        $updateResponse->assertSessionHas('success');
        $item->refresh();
        $this->assertEquals('Optimize Redis cache - v2', $item->title);
        $this->assertEquals('urgent', $item->priority);

        // 6. Delete improvement item
        $deleteResponse = $this->actingAs($this->superAdmin)
            ->delete(route('super.improvements.destroy', $item->id));

        $deleteResponse->assertSessionHas('success');
        $this->assertSoftDeleted('system_improvements', ['id' => $item->id]);
    }

    public function test_super_admin_can_view_cross_school_feedback_and_resolve_ticket()
    {
        // Create feedback ticket for the school
        $feedback = Feedback::create([
            'school_id'      => $this->school->id,
            'user_id'        => $this->superAdmin->id,
            'submitter_name' => 'John Doe (Principal)',
            'submitter_role' => 'Principal',
            'is_anonymous'   => false,
            'category'       => 'feature_request',
            'priority'       => 'high',
            'title'          => 'Add bulk SMS alert for exam routines',
            'description'    => 'We need an automated button to send SMS to parents when routine is published.',
            'status'         => 'pending',
        ]);

        // 1. Visit cross-school feedback index
        $indexResponse = $this->actingAs($this->superAdmin)
            ->get(route('super.feedback.index'));

        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('User Feedback &amp; Grievances', false);
        $indexResponse->assertSee('Add bulk SMS alert for exam routines');
        $indexResponse->assertSee('Happy Land English Boarding School Siraha');

        // 2. View ticket detail
        $showResponse = $this->actingAs($this->superAdmin)
            ->get(route('super.feedback.show', $feedback->id));

        $showResponse->assertStatus(200);
        $showResponse->assertSee('Add bulk SMS alert for exam routines');

        // 3. Update status to resolved with admin response
        $updateResponse = $this->actingAs($this->superAdmin)
            ->patch(route('super.feedback.status', $feedback->id), [
                'status'         => 'resolved',
                'admin_response' => 'Scheduled for release in next minor update (v2.4).',
            ]);

        $updateResponse->assertSessionHas('success');

        $feedback->refresh();
        $this->assertEquals('resolved', $feedback->status);
        $this->assertEquals('Scheduled for release in next minor update (v2.4).', $feedback->admin_response);
        $this->assertEquals($this->superAdmin->id, $feedback->resolved_by);
        $this->assertNotNull($feedback->resolved_at);
    }
}
