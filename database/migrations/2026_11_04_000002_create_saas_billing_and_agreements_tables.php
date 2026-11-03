<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Services & Hardware provided by Platform (Thermal printer, CCTV, Computer Setup, etc.)
        if (!Schema::hasTable('saas_services')) {
            Schema::create('saas_services', function (Blueprint $table) {
                $table->id();
                $table->string('name');                      // e.g. Thermal Printer 80mm
                $table->string('name_np')->nullable();       // e.g. थर्मल बिल प्रिन्टर
                $table->string('code')->unique();            // e.g. SRV-THERMAL-PRINTER
                $table->string('category')->default('hardware'); // hardware, software, service, setup, package
                $table->decimal('default_price', 12, 2)->default(0);
                $table->string('billing_basis')->default('one_time'); // one_time, annual, monthly, per_unit
                $table->string('unit_name')->default('थान (Unit)');
                $table->text('description')->nullable();
                $table->text('specifications')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_featured')->default(false);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 2. Service Agreements (विद्यालय व्यवस्थापन प्रणालीसम्बन्धी सेवा सम्झौता)
        if (!Schema::hasTable('saas_agreements')) {
            Schema::create('saas_agreements', function (Blueprint $table) {
                $table->id();
                $table->string('agreement_number')->unique(); // e.g. NSH-AGR-2083-001
                $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
                $table->string('status')->default('active');   // draft, active, renewed, expired, terminated

                // Nepali BS Date & English Dates
                $table->string('agreement_date_bs')->default('२०८३ साल बैशाख ०१ गते');
                $table->string('bs_year')->default('२०८३');
                $table->string('bs_month')->default('बैशाख');
                $table->string('bs_day')->default('०१');
                $table->date('effective_date')->nullable();
                $table->date('expiry_date')->nullable();
                $table->integer('duration_years')->default(1);

                // Service Provider (सेवा प्रदायक) details
                $table->string('provider_name')->default('टेक प्रोकोड प्रा. लि. (नेपालस्कुलहब)');
                $table->string('provider_reg_no')->default('३९१५१२/८२/८३');
                $table->string('provider_pan_vat')->default('६२३६०८२७२');
                $table->string('provider_address')->default('सिरहा / काठमाडौँ, नेपाल');
                $table->string('provider_phone')->default('+९७७ ९८०५९१६५९८');
                $table->string('provider_email')->default('support@techprocod.com.np');
                $table->string('provider_website')->default('www.techprocod.com.np');
                $table->string('provider_representative')->nullable()->default('प्रबन्ध निर्देशक');
                $table->string('provider_designation')->nullable()->default('अधिकृत प्रतिनिधि');

                // Client School (सेवा लिने विद्यालय) details
                $table->string('school_name');
                $table->string('school_address')->nullable();
                $table->string('school_reg_no')->nullable();
                $table->string('school_pan_no')->nullable();
                $table->string('school_representative')->nullable();
                $table->string('school_designation')->nullable();
                $table->string('school_phone')->nullable();
                $table->string('school_email')->nullable();

                // Standard Fees matching Section 2 & 12
                $table->decimal('setup_fee', 12, 2)->default(15000.00);
                $table->string('setup_fee_basis')->default('पहिलो वर्षका लागि एकपटक');
                $table->decimal('renewal_fee', 12, 2)->default(10000.00);
                $table->string('renewal_fee_basis')->default('दोस्रो वर्षदेखि प्रतिवर्ष');
                $table->string('software_inclusion_text')->default('समावेश (माथि उल्लिखित शुल्कभित्रै)');

                // Totals
                $table->decimal('additional_services_total', 12, 2)->default(0);
                $table->decimal('grand_total', 12, 2)->default(25000.00);
                $table->text('notes')->nullable();

                // Witnesses (साक्षीहरू)
                $table->string('witness_1_name')->nullable();
                $table->string('witness_1_date')->nullable();
                $table->string('witness_2_name')->nullable();
                $table->string('witness_2_date')->nullable();

                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 3. Agreement Items (Services/Hardware attached to an agreement)
        if (!Schema::hasTable('saas_agreement_items')) {
            Schema::create('saas_agreement_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('agreement_id')->constrained('saas_agreements')->cascadeOnDelete();
                $table->foreignId('service_id')->nullable()->constrained('saas_services')->nullOnDelete();
                $table->string('item_name');
                $table->decimal('unit_price', 12, 2)->default(0);
                $table->integer('quantity')->default(1);
                $table->decimal('total_price', 12, 2)->default(0);
                $table->string('basis')->default('एकपटक');
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }

        // 4. SaaS Invoices / Bills for Schools
        if (!Schema::hasTable('saas_invoices')) {
            Schema::create('saas_invoices', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_number')->unique(); // e.g. NSH-INV-2083-001
                $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
                $table->foreignId('agreement_id')->nullable()->constrained('saas_agreements')->nullOnDelete();
                $table->string('school_name');
                $table->string('school_pan')->nullable();
                $table->string('school_address')->nullable();
                $table->string('school_email')->nullable();
                $table->string('school_phone')->nullable();
                $table->date('invoice_date');
                $table->date('due_date')->nullable();
                $table->string('invoice_date_bs')->nullable();
                $table->string('due_date_bs')->nullable();
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('discount_amount', 12, 2)->default(0);
                $table->decimal('tax_percent', 5, 2)->default(0);
                $table->decimal('tax_amount', 12, 2)->default(0);
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->decimal('paid_amount', 12, 2)->default(0);
                $table->decimal('due_amount', 12, 2)->default(0);
                $table->string('status')->default('unpaid'); // draft, unpaid, partial, paid, overdue, cancelled
                $table->text('notes')->nullable();
                $table->text('terms')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 5. SaaS Invoice Line Items
        if (!Schema::hasTable('saas_invoice_items')) {
            Schema::create('saas_invoice_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('saas_invoice_id')->constrained('saas_invoices')->cascadeOnDelete();
                $table->foreignId('service_id')->nullable()->constrained('saas_services')->nullOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->decimal('quantity', 10, 2)->default(1);
                $table->decimal('unit_price', 12, 2)->default(0);
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->timestamps();
            });
        }

        // 6. SaaS Payment Records (Payments received from Schools)
        if (!Schema::hasTable('saas_payments')) {
            Schema::create('saas_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('saas_invoice_id')->constrained('saas_invoices')->cascadeOnDelete();
                $table->string('payment_number')->unique();
                $table->date('payment_date');
                $table->string('payment_date_bs')->nullable();
                $table->decimal('amount', 12, 2);
                $table->string('payment_method')->default('bank_transfer'); // bank_transfer, esewa, khalti, cash, cheque, connect_ips
                $table->string('transaction_reference')->nullable();
                $table->string('bank_name')->nullable();
                $table->text('note')->nullable();
                $table->unsignedBigInteger('received_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_payments');
        Schema::dropIfExists('saas_invoice_items');
        Schema::dropIfExists('saas_invoices');
        Schema::dropIfExists('saas_agreement_items');
        Schema::dropIfExists('saas_agreements');
        Schema::dropIfExists('saas_services');
    }
};

