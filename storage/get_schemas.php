<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$models = ['StudentAcademicInfo', 'StudentParentInfo', 'AssignedTeacher', 'Routine', 'GradingSystem', 'GradeRule', 'Exam', 'ExamRule', 'Mark', 'Syllabus', 'Assignment', 'Event', 'Notice'];
foreach ($models as $model) {
    echo $model . ': ';
    $class = '\\App\\Models\\' . $model;
    if (class_exists($class)) {
        $instance = new $class;
        echo implode(', ', \Illuminate\Support\Facades\Schema::getColumnListing($instance->getTable())) . PHP_EOL;
    } else {
        echo "Class not found\n";
    }
}
