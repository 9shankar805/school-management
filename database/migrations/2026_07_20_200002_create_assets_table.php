<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetsTable extends Migration
{
    public function up()
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('asset_tag')->unique()->nullable();   // barcode/tag
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('brand')->nullable();
            $table->string('model_number')->nullable();
            $table->string('serial_number')->nullable()->unique();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->date('warranty_expiry')->nullable();
            $table->string('location')->nullable();        // room / dept assigned to
            $table->unsignedBigInteger('assigned_to')->nullable(); // users.id
            $table->enum('condition', ['new','good','fair','poor','disposed'])->default('good');
            $table->enum('status', ['available','assigned','maintenance','disposed'])->default('available');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('asset_categories')->onDelete('set null');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->index(['status','condition']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('assets');
    }
}
