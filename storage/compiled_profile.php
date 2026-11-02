<?php <?php $__env->startSection('content'); ?>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="<?php echo e(route('home')); ?>" class="hover:text-indigo-600">Home</a> <span class="mx-1">/</span>
            <a href="<?php echo e(route('teacher.list.show')); ?>" class="hover:text-indigo-600">Teachers</a> <span class="mx-1">/</span>
            <span class="text-slate-600"><?php echo e($teacher->full_name); ?></span>
        </nav>

        <?php if(session('status')): ?>
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm"><i class="bi bi-check-circle me-1"></i><?php echo e(session('status')); ?></div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
        <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-sm"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><p><?php echo e($e); ?></p><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div>
        <?php endif; ?>

        
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6 flex flex-wrap items-start gap-6">
            <img src="<?php echo e($teacher->avatar); ?>" class="w-20 h-20 rounded-2xl object-cover flex-shrink-0 border-2 border-indigo-100" alt="">
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-3 mb-1">
                    <h1 class="text-xl font-bold text-slate-800"><?php echo e($teacher->full_name); ?></h1>
                    <span class="text-xs bg-blue-100 text-blue-700 px-2.5 py-0.5 rounded-full font-medium capitalize"><?php echo e(str_replace('-',' ',$teacher->primary_role)); ?></span>
                    <?php if($teacher->departments->count()): ?>
                    <?php $__currentLoopData = $teacher->departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full"><?php echo e($d->name); ?></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </div>
                <div class="flex flex-wrap gap-4 text-xs text-slate-500 mt-1">
                    <span><i class="bi bi-envelope me-1"></i><?php echo e($teacher->email); ?></span>
                    <?php if($teacher->phone): ?><span><i class="bi bi-telephone me-1"></i><?php echo e($teacher->phone); ?></span><?php endif; ?>
                    <?php if($activeContract): ?><span><i class="bi bi-briefcase me-1"></i><?php echo e(ucfirst($activeContract->position ?? $activeContract->contract_type)); ?> · $<?php echo e(number_format($activeContract->basic_salary)); ?>/mo</span><?php endif; ?>
                </div>
                <div class="flex flex-wrap gap-4 mt-3">
                    <div class="text-center"><p class="text-lg font-bold text-slate-800"><?php echo e($teacher->qualifications->count()); ?></p><p class="text-[10px] text-slate-400">Qualifications</p></div>
                    <div class="text-center"><p class="text-lg font-bold text-indigo-600"><?php echo e($teacher->assignedCourses->count()); ?></p><p class="text-[10px] text-slate-400">Courses</p></div>
                    <div class="text-center"><p class="text-lg font-bold <?php echo e(($attendanceStats['pct'] ?? 100) >= 75 ? 'text-emerald-600' : 'text-rose-600'); ?>"><?php echo e($attendanceStats['pct'] ?? '—'); ?>%</p><p class="text-[10px] text-slate-400">Attendance</p></div>
                    <div class="text-center"><p class="text-lg font-bold text-violet-600"><?php echo e($teacher->performanceReviews->first()?->overall_rating ?? '—'); ?></p><p class="text-[10px] text-slate-400">Last Review</p></div>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 flex-shrink-0">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create teachers')): ?>
                <a href="<?php echo e(route('teacher.edit.show', $teacher->id)); ?>" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium transition"><i class="bi bi-pencil"></i> Edit</a>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="flex gap-1 overflow-x-auto mb-6 bg-white rounded-xl border border-slate-100 shadow-sm p-1">
            <?php $__currentLoopData = [
                ['overview','bi-person-lines-fill','Overview'],
                ['qualifications','bi-award','Qualifications'],
                ['contracts','bi-file-earmark-text','Contracts'],
                ['documents','bi-folder2-open','Documents'],
                ['leave','bi-calendar-x','Leave'],
                ['attendance','bi-calendar-check','Attendance'],
                ['payroll','bi-cash-stack','Payroll'],
                ['performance','bi-graph-up','Performance'],
                ['training','bi-book','Training'],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$id,$icon,$label]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button onclick="showTab('<?php echo e($id); ?>')" id="tab-btn-<?php echo e($id); ?>"
                class="tab-btn flex-shrink-0 flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium transition whitespace-nowrap text-slate-500 hover:text-slate-700 hover:bg-slate-50">
                <i class="bi <?php echo e($icon); ?>"></i> <?php echo e($label); ?>

            </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <div id="tab-overview" class="tab-panel">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-slate-100 text-sm font-semibold text-slate-700"><i class="bi bi-person me-1 text-indigo-500"></i>Personal Info</div>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-slate-50">
                            <?php $__currentLoopData = [['Gender',$teacher->gender],['Birthday',$teacher->birthday?->format('d M Y')],['Nationality',$teacher->nationality],['Religion',$teacher->religion],['Blood Type',$teacher->blood_type],['Address',trim(($teacher->address??'').' '.($teacher->city??''))]]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$l,$v]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-slate-50"><td class="px-5 py-2.5 text-slate-400 font-medium w-36"><?php echo e($l); ?></td><td class="px-5 py-2.5 text-slate-700"><?php echo e($v ?: '—'); ?></td></tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <div class="space-y-4">
                    
                    <?php if($activeContract): ?>
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-5 py-3 border-b border-slate-100 text-sm font-semibold text-slate-700"><i class="bi bi-briefcase me-1 text-emerald-500"></i>Active Contract</div>
                        <table class="w-full text-sm">
                            <tbody class="divide-y divide-slate-50">
                                <tr class="hover:bg-slate-50"><td class="px-5 py-2.5 text-slate-400 font-medium w-36">Type</td><td class="px-5 py-2.5 text-slate-700"><?php echo e(ucfirst($activeContract->contract_type)); ?></td></tr>
                                <tr class="hover:bg-slate-50"><td class="px-5 py-2.5 text-slate-400 font-medium">Position</td><td class="px-5 py-2.5 text-slate-700"><?php echo e($activeContract->position ?? '—'); ?></td></tr>
                                <tr class="hover:bg-slate-50"><td class="px-5 py-2.5 text-slate-400 font-medium">Salary</td><td class="px-5 py-2.5 text-slate-700">$<?php echo e(number_format($activeContract->basic_salary)); ?>/month</td></tr>
                                <tr class="hover:bg-slate-50"><td class="px-5 py-2.5 text-slate-400 font-medium">Start</td><td class="px-5 py-2.5 text-slate-700"><?php echo e($activeContract->start_date->format('d M Y')); ?></td></tr>
                                <tr class="hover:bg-slate-50"><td class="px-5 py-2.5 text-slate-400 font-medium">End</td>
                                    <td class="px-5 py-2.5">
                                        <?php if($activeContract->end_date): ?>
                                        <span class="<?php echo e($activeContract->is_expiring ? 'text-amber-600 font-medium' : 'text-slate-700'); ?>">
                                            <?php echo e($activeContract->end_date->format('d M Y')); ?>

                                            <?php if($activeContract->is_expiring): ?> <i class="bi bi-exclamation-triangle-fill text-amber-500 ms-1" title="Expiring soon"></i><?php endif; ?>
                                        </span>
                                        <?php else: ?> <span class="text-slate-400">Permanent</span><?php endif; ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                    
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-5 py-3 border-b border-slate-100 text-sm font-semibold text-slate-700"><i class="bi bi-journal-medical me-1 text-blue-500"></i>Assigned Courses</div>
                        <?php if($teacher->assignedCourses->count()): ?>
                        <div class="divide-y divide-slate-50">
                            <?php $__currentLoopData = $teacher->assignedCourses->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ac): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="px-5 py-2.5 flex justify-between text-sm">
                                <span class="text-slate-700"><?php echo e($ac->course?->name ?? '—'); ?></span>
                                <span class="text-slate-400"><?php echo e($ac->section?->section_name ?? '—'); ?></span>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <?php else: ?><p class="text-sm text-slate-400 text-center py-6">No courses assigned.</p><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        
        <div id="tab-qualifications" class="tab-panel hidden">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create teachers')): ?>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-plus-lg me-1 text-indigo-500"></i>Add Qualification</p>
                <form method="POST" action="<?php echo e(route('teacher.qualifications.store', $teacher->id)); ?>" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php echo csrf_field(); ?>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Type</label>
                    <select name="type" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <?php $__currentLoopData = \App\Models\TeacherQualification::TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($v); ?>"><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Title *</label><input type="text" name="title" required placeholder="e.g. BSc Mathematics" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Institution *</label><input type="text" name="institution" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Field of Study</label><input type="text" name="field_of_study" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Start Year</label><input type="number" name="start_year" min="1950" max="<?php echo e(now()->year); ?>" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">End Year</label><input type="number" name="end_year" min="1950" max="<?php echo e(now()->year + 5); ?>" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Grade / Result</label><input type="text" name="grade" placeholder="e.g. First Class, 3.8 GPA" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Attachment (optional)</label><input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm"></div>
                    <div class="md:col-span-2"><button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">Add Qualification</button></div>
                </form>
            </div>
            <?php endif; ?>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 text-sm font-semibold text-slate-700">Academic Qualifications (<?php echo e($teacher->qualifications->count()); ?>)</div>
                <?php if($teacher->qualifications->count()): ?>
                <div class="divide-y divide-slate-50">
                    <?php $__currentLoopData = $teacher->qualifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="px-5 py-4 flex items-start gap-3">
                        <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm flex-shrink-0"><i class="bi bi-mortarboard"></i></span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-700"><?php echo e($q->title); ?></p>
                            <p class="text-xs text-slate-500"><?php echo e($q->institution); ?><?php if($q->field_of_study): ?> · <?php echo e($q->field_of_study); ?><?php endif; ?></p>
                            <p class="text-xs text-slate-400 mt-0.5"><?php echo e($q->start_year ?? ''); ?> <?php if($q->end_year): ?> – <?php echo e($q->end_year); ?> <?php endif; ?> <?php if($q->grade): ?> · <?php echo e($q->grade); ?> <?php endif; ?></p>
                            <?php if($q->attachment_path): ?><a href="<?php echo e($q->attachment_url); ?>" target="_blank" class="text-xs text-indigo-600 hover:underline mt-0.5 inline-block"><i class="bi bi-paperclip me-1"></i>Certificate</a><?php endif; ?>
                        </div>
                        <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full flex-shrink-0"><?php echo e(\App\Models\TeacherQualification::TYPES[$q->type] ?? $q->type); ?></span>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create teachers')): ?>
                        <form method="POST" action="<?php echo e(route('teacher.qualifications.destroy', $q->id)); ?>"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="text-xs text-rose-400 hover:text-rose-600 flex-shrink-0" onclick="return confirm('Remove?')"><i class="bi bi-trash"></i></button>
                        </form>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php else: ?><p class="text-sm text-slate-400 text-center py-10">No qualifications added.</p><?php endif; ?>
            </div>
        </div>

        
        <div id="tab-contracts" class="tab-panel hidden">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create teachers')): ?>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-file-earmark-plus me-1 text-emerald-500"></i>New Contract</p>
                <form method="POST" action="<?php echo e(route('teacher.contracts.store', $teacher->id)); ?>" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php echo csrf_field(); ?>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Contract Type</label>
                    <select name="contract_type" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <?php $__currentLoopData = \App\Models\TeacherContract::TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($v); ?>"><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Position</label><input type="text" name="position" placeholder="e.g. Senior Teacher" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Start Date *</label><input type="date" name="start_date" required value="<?php echo e(now()->toDateString()); ?>" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">End Date</label><input type="date" name="end_date" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Basic Salary ($/month) *</label><input type="number" name="basic_salary" step="0.01" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Attachment</label><input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm"></div>
                    <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-500 mb-1">Terms</label><textarea name="terms" rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea></div>
                    <div class="md:col-span-2"><button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition">Create Contract</button></div>
                </form>
            </div>
            <?php endif; ?>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 text-sm font-semibold text-slate-700">Contract History</div>
                <?php if($teacher->contracts->count()): ?>
                <div class="divide-y divide-slate-50">
                    <?php $__currentLoopData = $teacher->contracts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="px-5 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <span class="text-sm font-semibold text-slate-700"><?php echo e($c->position ?? \App\Models\TeacherContract::TYPES[$c->contract_type]); ?></span>
                                    <span class="text-xs px-2 py-0.5 rounded-full font-medium <?php echo e($c->status_badge); ?>"><?php echo e(ucfirst($c->status)); ?></span>
                                    <?php if($c->is_expiring): ?><span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full"><i class="bi bi-exclamation-triangle me-1"></i>Expiring soon</span><?php endif; ?>
                                    <?php if($c->is_expired): ?><span class="text-xs bg-rose-100 text-rose-700 px-2 py-0.5 rounded-full"><i class="bi bi-x-circle me-1"></i>Expired</span><?php endif; ?>
                                </div>
                                <p class="text-xs text-slate-500"><?php echo e($c->start_date->format('d M Y')); ?> – <?php echo e($c->end_date?->format('d M Y') ?? 'Ongoing'); ?> · $<?php echo e(number_format($c->basic_salary)); ?>/month</p>
                            </div>
                            <?php if($c->attachment_path): ?><a href="<?php echo e(\Illuminate\Support\Facades\Storage::url($c->attachment_path)); ?>" target="_blank" class="text-xs text-indigo-600 hover:underline flex-shrink-0">View</a><?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php else: ?><p class="text-sm text-slate-400 text-center py-10">No contracts on record.</p><?php endif; ?>
            </div>
        </div>

        
        <div id="tab-documents" class="tab-panel hidden">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create teachers')): ?>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-upload me-1 text-indigo-500"></i>Upload Document</p>
                <form method="POST" action="<?php echo e(route('teacher.documents.store', $teacher->id)); ?>" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php echo csrf_field(); ?>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Document Type</label>
                    <select name="document_type" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <?php $__currentLoopData = \App\Models\TeacherDocument::TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($v); ?>"><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Title *</label><input type="text" name="title" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-500 mb-1">File (PDF/JPG/PNG, max 10MB)</label><input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm"></div>
                    <div class="md:col-span-2"><button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">Upload</button></div>
                </form>
            </div>
            <?php endif; ?>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 text-sm font-semibold text-slate-700"><i class="bi bi-folder2-open me-1 text-amber-500"></i>Documents (<?php echo e($teacher->teacherDocuments->count()); ?>)</div>
                <?php if($teacher->teacherDocuments->count()): ?>
                <div class="divide-y divide-slate-50">
                    <?php $__currentLoopData = $teacher->teacherDocuments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="px-5 py-3 flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg <?php echo e($doc->isPdf() ? 'bg-rose-50 text-rose-600' : 'bg-blue-50 text-blue-600'); ?> flex items-center justify-center text-sm flex-shrink-0">
                            <i class="bi <?php echo e($doc->isPdf() ? 'bi-file-earmark-pdf' : 'bi-file-earmark-image'); ?>"></i>
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-700 truncate"><?php echo e($doc->title); ?></p>
                            <p class="text-xs text-slate-400"><?php echo e(\App\Models\TeacherDocument::TYPES[$doc->document_type] ?? $doc->document_type); ?> · <?php echo e($doc->human_size); ?></p>
                        </div>
                        <?php if($doc->is_verified): ?><span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full flex-shrink-0"><i class="bi bi-patch-check-fill"></i> Verified</span><?php endif; ?>
                        <div class="flex gap-2 flex-shrink-0">
                            <a href="<?php echo e($doc->url); ?>" target="_blank" class="text-xs text-indigo-600 hover:underline">View</a>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create teachers')): ?>
                            <form method="POST" action="<?php echo e(route('teacher.documents.verify', $doc->id)); ?>"><?php echo csrf_field(); ?><button class="text-xs text-slate-500 hover:text-slate-700"><?php echo e($doc->is_verified ? 'Unverify' : 'Verify'); ?></button></form>
                            <form method="POST" action="<?php echo e(route('teacher.documents.destroy', $doc->id)); ?>"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="text-xs text-rose-500 hover:text-rose-700" onclick="return confirm('Delete?')">Delete</button></form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php else: ?><p class="text-sm text-slate-400 text-center py-10">No documents uploaded.</p><?php endif; ?>
            </div>
        </div>

        
        <div id="tab-leave" class="tab-panel hidden">
            
            <?php $balances = $teacher->leaveBalances()->with('leaveType')->get(); ?>
            <?php if($balances->count()): ?>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
                <?php $__currentLoopData = $balances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
                    <p class="text-xs text-slate-400 mb-1"><?php echo e($b->leaveType->name); ?></p>
                    <p class="text-2xl font-bold text-indigo-600"><?php echo e($b->remaining_days); ?></p>
                    <p class="text-xs text-slate-400">of <?php echo e($b->total_days); ?> days left</p>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php endif; ?>

            
            <?php if(auth()->id() === $teacher->id || auth()->user()->can('create teachers')): ?>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-calendar-plus me-1 text-indigo-500"></i>Apply for Leave</p>
                <form method="POST" action="<?php echo e(route('leave.apply')); ?>" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php echo csrf_field(); ?>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Leave Type *</label>
                    <select name="leave_type_id" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <?php $__currentLoopData = \App\Models\LeaveType::where('is_active',true)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($lt->id); ?>"><?php echo e($lt->name); ?> (<?php echo e($lt->days_allowed); ?> days/year)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select></div>
                    <div></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">From Date *</label><input type="date" name="from_date" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">To Date *</label><input type="date" name="to_date" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-500 mb-1">Reason *</label><textarea name="reason" rows="2" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea></div>
                    <div class="md:col-span-2"><button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">Submit Application</button></div>
                </form>
            </div>
            <?php endif; ?>

            
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 text-sm font-semibold text-slate-700">Leave History</div>
                <?php $leaves = $teacher->leaveApplications()->with('leaveType','reviewer')->get(); ?>
                <?php if($leaves->count()): ?>
                <div class="divide-y divide-slate-50">
                    <?php $__currentLoopData = $leaves; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $la): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="px-5 py-3 flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium <?php echo e($la->status_badge); ?>"><?php echo e(ucfirst($la->status)); ?></span>
                                <span class="text-sm font-medium text-slate-700"><?php echo e($la->leaveType->name); ?></span>
                                <span class="text-xs text-slate-400"><?php echo e($la->total_days); ?> day(s)</span>
                            </div>
                            <p class="text-xs text-slate-500"><?php echo e($la->from_date->format('d M Y')); ?> – <?php echo e($la->to_date->format('d M Y')); ?></p>
                            <p class="text-xs text-slate-400 mt-0.5"><?php echo e(\Illuminate\Support\Str::limit($la->reason, 80)); ?></p>
                            <?php if($la->reviewer_notes): ?><p class="text-xs text-slate-400 mt-0.5 italic">Note: <?php echo e($la->reviewer_notes); ?></p><?php endif; ?>
                        </div>
                        <div class="flex-shrink-0 text-right">
                            <?php if($la->status === 'pending'): ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create teachers')): ?>
                            <form method="POST" action="<?php echo e(route('leave.review', $la->id)); ?>" class="inline-flex gap-1">
                                <?php echo csrf_field(); ?><input type="hidden" name="status" value="approved">
                                <button class="text-xs px-2 py-1 bg-emerald-100 text-emerald-700 hover:bg-emerald-200 rounded-lg transition">Approve</button>
                            </form>
                            <form method="POST" action="<?php echo e(route('leave.review', $la->id)); ?>" class="inline-flex gap-1">
                                <?php echo csrf_field(); ?><input type="hidden" name="status" value="rejected">
                                <button class="text-xs px-2 py-1 bg-rose-100 text-rose-700 hover:bg-rose-200 rounded-lg transition">Reject</button>
                            </form>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php else: ?><p class="text-sm text-slate-400 text-center py-8">No leave applications.</p><?php endif; ?>
            </div>
        </div>

        
        <div id="tab-attendance" class="tab-panel hidden">
            <?php
                $thisMonth = now()->month; $thisYear = now()->year;
                $monthRecords = $teacher->teacherAttendance()->whereMonth('date',$thisMonth)->whereYear('date',$thisYear)->get();
                $attSummary = ['present'=>$monthRecords->where('status','present')->count(),'absent'=>$monthRecords->where('status','absent')->count(),'late'=>$monthRecords->where('status','late')->count(),'on_leave'=>$monthRecords->where('status','on_leave')->count()];
                $total = $monthRecords->count(); $pct = $total > 0 ? round($attSummary['present']/$total*100) : 0;
            ?>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
                <?php $__currentLoopData = ['present'=>['emerald','Present'],'absent'=>['rose','Absent'],'late'=>['amber','Late'],'on_leave'=>['violet','On Leave']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s=>[$c,$l]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
                    <p class="text-2xl font-bold text-<?php echo e($c); ?>-600"><?php echo e($attSummary[$s]); ?></p>
                    <p class="text-xs text-slate-400 mt-0.5"><?php echo e($l); ?> (<?php echo e(now()->format('M Y')); ?>)</p>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-5">
                <p class="text-sm font-semibold text-slate-700 mb-3">Monthly Attendance Rate</p>
                <div class="h-3 bg-slate-100 rounded-full overflow-hidden mb-1">
                    <div class="h-full rounded-full <?php echo e($pct >= 75 ? 'bg-emerald-500' : 'bg-rose-500'); ?>" style="width:<?php echo e($pct); ?>%"></div>
                </div>
                <p class="text-xs text-slate-400"><?php echo e($pct); ?>% present this month · <a href="<?php echo e(route('teacher.attendance.show', $teacher->id)); ?>" class="text-indigo-600 hover:underline">Full report</a></p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 text-sm font-semibold text-slate-700">Recent Attendance</div>
                <?php if($monthRecords->count()): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm"><thead><tr class="text-left text-xs text-slate-400 bg-slate-50">
                        <th class="px-5 py-2.5 font-medium">Date</th><th class="px-5 py-2.5 font-medium">Status</th>
                        <th class="px-5 py-2.5 font-medium">Check In</th><th class="px-5 py-2.5 font-medium">Check Out</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php $__currentLoopData = $monthRecords->sortByDesc('date')->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-2.5 text-slate-600"><?php echo e($rec->date->format('d M Y')); ?></td>
                            <td class="px-5 py-2.5"><span class="text-xs px-2 py-0.5 rounded-full font-medium <?php echo e($rec->status_badge); ?>"><?php echo e(\App\Models\TeacherAttendance::STATUSES[$rec->status] ?? $rec->status); ?></span></td>
                            <td class="px-5 py-2.5 text-slate-500 font-mono text-xs"><?php echo e($rec->check_in ?? '—'); ?></td>
                            <td class="px-5 py-2.5 text-slate-500 font-mono text-xs"><?php echo e($rec->check_out ?? '—'); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody></table>
                </div>
                <?php else: ?><p class="text-sm text-slate-400 text-center py-8">No attendance records this month.</p><?php endif; ?>
            </div>
        </div>

        
        <div id="tab-payroll" class="tab-panel hidden">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-cash-stack me-1 text-emerald-500"></i>Payroll History</p>
                    <a href="<?php echo e(route('teacher.payroll.index')); ?>" class="text-xs text-indigo-600 hover:underline">Manage Payroll</a>
                </div>
                <?php $payrolls = $teacher->payrolls()->take(12)->get(); ?>
                <?php if($payrolls->count()): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm"><thead><tr class="text-left text-xs text-slate-400 bg-slate-50">
                        <th class="px-5 py-2.5 font-medium">Period</th><th class="px-5 py-2.5 font-medium">Gross</th>
                        <th class="px-5 py-2.5 font-medium">Deductions</th><th class="px-5 py-2.5 font-medium">Net</th>
                        <th class="px-5 py-2.5 font-medium">Status</th><th class="px-5 py-2.5 font-medium"></th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php $__currentLoopData = $payrolls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-medium text-slate-700"><?php echo e($p->month_name); ?> <?php echo e($p->year); ?></td>
                            <td class="px-5 py-3 text-slate-600">$<?php echo e(number_format($p->gross_salary)); ?></td>
                            <td class="px-5 py-3 text-rose-500">-$<?php echo e(number_format($p->tax_deduction + $p->other_deductions)); ?></td>
                            <td class="px-5 py-3 font-bold text-emerald-600">$<?php echo e(number_format($p->net_salary)); ?></td>
                            <td class="px-5 py-3"><span class="text-xs px-2 py-0.5 rounded-full font-medium <?php echo e($p->status_badge); ?>"><?php echo e(ucfirst($p->status)); ?></span></td>
                            <td class="px-5 py-3"><a href="<?php echo e(route('teacher.payroll.slip', $p->id)); ?>" target="_blank" class="text-xs text-indigo-600 hover:underline">Slip</a></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody></table>
                </div>
                <?php else: ?><p class="text-sm text-slate-400 text-center py-10">No payroll records.</p><?php endif; ?>
            </div>
        </div>

        
        <div id="tab-performance" class="tab-panel hidden">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create teachers')): ?>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-graph-up me-1 text-indigo-500"></i>New Review</p>
                <form method="POST" action="<?php echo e(route('teacher.reviews.store', $teacher->id)); ?>" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php echo csrf_field(); ?>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Review Period *</label><input type="text" name="review_period" required placeholder="e.g. 2025-Q1" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Review Date *</label><input type="date" name="review_date" required value="<?php echo e(now()->toDateString()); ?>" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <?php $__currentLoopData = ['teaching_quality'=>'Teaching Quality','punctuality'=>'Punctuality','student_engagement'=>'Student Engagement','communication'=>'Communication','professionalism'=>'Professionalism']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1"><?php echo e($label); ?> (1–5) *</label>
                        <select name="<?php echo e($field); ?>" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <?php $__currentLoopData = [5=>'5 - Excellent',4=>'4 - Good',3=>'3 - Average',2=>'2 - Below Average',1=>'1 - Poor']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($v); ?>"><?php echo e($l); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php $__currentLoopData = ['strengths'=>'Strengths','areas_for_improvement'=>'Areas for Improvement','goals'=>'Goals','reviewer_comments'=>'Comments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-500 mb-1"><?php echo e($l); ?></label><textarea name="<?php echo e($f); ?>" rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <div class="md:col-span-2"><button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">Submit Review</button></div>
                </form>
            </div>
            <?php endif; ?>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 text-sm font-semibold text-slate-700">Performance Reviews (<?php echo e($teacher->performanceReviews->count()); ?>)</div>
                <?php if($teacher->performanceReviews->count()): ?>
                <div class="divide-y divide-slate-50">
                    <?php $__currentLoopData = $teacher->performanceReviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="px-5 py-4">
                        <div class="flex items-start justify-between gap-3 mb-2">
                            <div>
                                <p class="text-sm font-semibold text-slate-700"><?php echo e($rev->review_period); ?></p>
                                <p class="text-xs text-slate-400"><?php echo e($rev->review_date->format('d M Y')); ?> · By <?php echo e($rev->reviewer?->full_name ?? '—'); ?></p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-2xl font-bold <?php echo e($rev->rating_color); ?>"><?php echo e($rev->overall_rating); ?></p>
                                <p class="text-xs text-slate-400">/5 overall</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-5 gap-1 mb-2">
                            <?php $__currentLoopData = ['teaching_quality','punctuality','student_engagement','communication','professionalism']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="text-center">
                                <div class="h-10 bg-slate-100 rounded flex items-end overflow-hidden">
                                    <div class="w-full bg-indigo-500 rounded" style="height:<?php echo e(($rev->$field ?? 0) * 20); ?>%"></div>
                                </div>
                                <p class="text-[9px] text-slate-400 mt-0.5 truncate"><?php echo e(ucwords(str_replace('_',' ',$field))); ?></p>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <?php if($rev->strengths): ?><p class="text-xs text-slate-600"><span class="font-medium text-slate-700">Strengths:</span> <?php echo e(\Illuminate\Support\Str::limit($rev->strengths, 100)); ?></p><?php endif; ?>
                        <?php if($rev->areas_for_improvement): ?><p class="text-xs text-slate-500 mt-0.5"><span class="font-medium text-slate-600">Improve:</span> <?php echo e(\Illuminate\Support\Str::limit($rev->areas_for_improvement, 100)); ?></p><?php endif; ?>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php else: ?><p class="text-sm text-slate-400 text-center py-10">No performance reviews yet.</p><?php endif; ?>
            </div>
        </div>

        
        <div id="tab-training" class="tab-panel hidden">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create teachers')): ?>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-book me-1 text-blue-500"></i>Add Training Record</p>
                <form method="POST" action="<?php echo e(route('teacher.training.store', $teacher->id)); ?>" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php echo csrf_field(); ?>
                    <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-500 mb-1">Title *</label><input type="text" name="title" required placeholder="e.g. Modern Teaching Methods Workshop" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Type</label>
                    <select name="type" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <?php $__currentLoopData = \App\Models\TeacherTraining::TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($v); ?>"><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Organizer</label><input type="text" name="organizer" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">From Date *</label><input type="date" name="from_date" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">To Date</label><input type="date" name="to_date" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Hours</label><input type="number" name="hours" min="0" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Certificate No.</label><input type="text" name="certificate_no" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div class="md:col-span-2"><button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">Add Record</button></div>
                </form>
            </div>
            <?php endif; ?>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 text-sm font-semibold text-slate-700">Training Records (<?php echo e($teacher->trainingRecords->count()); ?>)</div>
                <?php if($teacher->trainingRecords->count()): ?>
                <div class="divide-y divide-slate-50">
                    <?php $__currentLoopData = $teacher->trainingRecords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="px-5 py-3 flex items-start gap-3">
                        <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm flex-shrink-0"><i class="bi bi-journal-bookmark"></i></span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-700"><?php echo e($tr->title); ?></p>
                            <p class="text-xs text-slate-400"><?php echo e(\App\Models\TeacherTraining::TYPES[$tr->type] ?? $tr->type); ?><?php if($tr->organizer): ?> · <?php echo e($tr->organizer); ?><?php endif; ?></p>
                            <p class="text-xs text-slate-400 mt-0.5"><?php echo e($tr->from_date->format('d M Y')); ?> <?php if($tr->to_date): ?> – <?php echo e($tr->to_date->format('d M Y')); ?> <?php endif; ?> <?php if($tr->hours): ?> · <?php echo e($tr->hours); ?>h <?php endif; ?></p>
                        </div>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create teachers')): ?>
                        <form method="POST" action="<?php echo e(route('teacher.training.destroy', $tr->id)); ?>"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="text-xs text-rose-400 hover:text-rose-600" onclick="return confirm('Remove?')"><i class="bi bi-trash"></i></button>
                        </form>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php else: ?><p class="text-sm text-slate-400 text-center py-10">No training records.</p><?php endif; ?>
            </div>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function showTab(id) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(b => { b.classList.remove('bg-indigo-50','text-indigo-700','font-semibold'); b.classList.add('text-slate-500'); });
    var panel = document.getElementById('tab-' + id);
    var btn = document.getElementById('tab-btn-' + id);
    if (panel) panel.classList.remove('hidden');
    if (btn) { btn.classList.add('bg-indigo-50','text-indigo-700','font-semibold'); btn.classList.remove('text-slate-500'); }
    history.replaceState(null,'','#' + id);
}
(function(){ showTab(location.hash.replace('#','') || 'overview'); })();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>