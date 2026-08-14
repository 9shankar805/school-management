@php
/*
 * Initial static preview rendered server-side at page load.
 * Uses dummy data so it's always viewable without a real student.
 */
$dummyData = [
    'name'       => 'Aarav Sharma',
    'class'      => 'Grade 10',
    'section'    => 'Science A',
    'gender'     => 'Male',
    'dob'        => 'Jan 15, 2010',
    'phone'      => '+977 98XXXXXXXX',
    'blood_type' => 'B+',
    'roll_no'    => 'ID-SCH-2026-001',
    'session'    => '2026-2027',
    'photoSrc'   => 'data:image/svg+xml;base64,' . base64_encode(
        '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">'
        .'<rect width="100" height="100" fill="#dbeafe"/>'
        .'<circle cx="50" cy="36" r="18" fill="#93c5fd"/>'
        .'<ellipse cx="50" cy="78" rx="28" ry="18" fill="#93c5fd"/>'
        .'</svg>'
    ),
    'qrSrc'      => 'data:image/svg+xml;base64,' . base64_encode(
        '<svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60">'
        .'<rect width="60" height="60" fill="#f1f5f9"/>'
        .'<text x="50%" y="55%" text-anchor="middle" font-size="8" fill="#94a3b8">QR</text>'
        .'</svg>'
    ),
];
@endphp
@include('id-card-templates.card-render', ['t' => $t, 'school' => $school, 'dummyData' => $dummyData])
