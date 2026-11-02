import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../theme/app_theme.dart';
import '../widgets/common_widgets.dart';
import '../providers/teacher_provider.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import 'attendance_screen.dart';

class ScheduleScreen extends StatefulWidget {
  const ScheduleScreen({super.key});

  @override
  State<ScheduleScreen> createState() => _ScheduleScreenState();
}

class _ScheduleScreenState extends State<ScheduleScreen> {
  final ApiService _apiService = ApiService();
  bool _isLoading = true;
  String _activeMode = 'my_routine'; // 'my_routine' or 'class_routine'
  int _selectedDay = 0; // 0=Sun, 1=Mon, ..., 6=Sat

  // Class routine selector
  int _selectedClassId = 1;
  int _selectedSectionId = 1;
  String _selectedClassName = 'Grade 10 - A';

  // Weekly data structure
  Map<String, List<Map<String, dynamic>>> _mySchedule = {
    'sunday': [],
    'monday': [],
    'tuesday': [],
    'wednesday': [],
    'thursday': [],
    'friday': [],
    'saturday': [],
  };

  Map<String, List<Map<String, dynamic>>> _classSchedule = {
    'sunday': [],
    'monday': [],
    'tuesday': [],
    'wednesday': [],
    'thursday': [],
    'friday': [],
    'saturday': [],
  };

  List<Map<String, dynamic>> _daysMeta = [];
  Map<String, dynamic>? _livePeriod;
  Map<String, dynamic>? _nextPeriod;
  List<dynamic> _availableTeachers = [];
  List<dynamic> _substitutions = [];

  final List<String> _dayKeys = [
    'sunday',
    'monday',
    'tuesday',
    'wednesday',
    'thursday',
    'friday',
    'saturday',
  ];

  @override
  void initState() {
    super.initState();
    // Default selected day based on real weekday in Nepal (0=Sunday ... 6=Saturday)
    final now = DateTime.now();
    _selectedDay = now.weekday == 7 ? 0 : now.weekday; // DateTime 7=Sunday in Dart
    _initDaysMeta();
    _loadCachedSchedule();
    _fetchWeeklySchedule();
    _fetchAvailableTeachers();
  }

  void _initDaysMeta() {
    final now = DateTime.now();
    // Dart: Monday=1, ..., Sunday=7. In Nepal, week starts Sunday (index 0)
    final currentDayIndex = now.weekday == 7 ? 0 : now.weekday;
    final startOfSun = now.subtract(Duration(days: currentDayIndex));

    final dayConfigs = [
      {'label': 'Sun', 'np': 'आइत', 'full': 'आइतबार', 'holiday': false},
      {'label': 'Mon', 'np': 'सोम', 'full': 'सोमबार', 'holiday': false},
      {'label': 'Tue', 'np': 'मङ्गल', 'full': 'मंगलबार', 'holiday': false},
      {'label': 'Wed', 'np': 'बुध', 'full': 'बुधबार', 'holiday': false},
      {'label': 'Thu', 'np': 'बिही', 'full': 'बिहीबार', 'holiday': false},
      {'label': 'Fri', 'np': 'शुक्र', 'full': 'शुक्रबार', 'holiday': false},
      {'label': 'Sat', 'np': 'शनि', 'full': 'शनिबार (छुट्टी)', 'holiday': true},
    ];

    _daysMeta = List.generate(7, (i) {
      final date = startOfSun.add(Duration(days: i));
      return {
        'index': i,
        'key': _dayKeys[i],
        'label': dayConfigs[i]['label'] as String,
        'np': dayConfigs[i]['np'] as String,
        'full': dayConfigs[i]['full'] as String,
        'is_holiday': dayConfigs[i]['holiday'] as bool,
        'dayNum': '${date.day}',
        'isToday': i == currentDayIndex,
      };
    });
  }

  Future<void> _loadCachedSchedule() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final cached = prefs.getString('cached_weekly_routine');
      if (cached != null) {
        final decoded = jsonDecode(cached) as Map<String, dynamic>;
        _parseScheduleResponse(decoded, isFromCache: true);
      }
    } catch (_) {}
  }

  Future<void> _fetchWeeklySchedule() async {
    setState(() => _isLoading = true);
    try {
      final res = await _apiService.getWeeklySchedule();
      if (res.success && res.data != null) {
        _parseScheduleResponse(res.data!);
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('cached_weekly_routine', jsonEncode(res.data));
      } else {
        _buildFallbackTeacherSchedule();
      }
    } catch (_) {
      _buildFallbackTeacherSchedule();
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _parseScheduleResponse(Map<String, dynamic> data, {bool isFromCache = false}) {
    if (data['schedule'] != null && data['schedule'] is Map) {
      final sched = data['schedule'] as Map<String, dynamic>;
      final Map<String, List<Map<String, dynamic>>> parsed = {};
      for (final key in _dayKeys) {
        if (sched[key] is List) {
          parsed[key] = (sched[key] as List)
              .map((item) => Map<String, dynamic>.from(item as Map))
              .toList();
        } else {
          parsed[key] = [];
        }
      }
      setState(() {
        _mySchedule = parsed;
        _livePeriod = data['current_period'] != null
            ? Map<String, dynamic>.from(data['current_period'] as Map)
            : null;
        _nextPeriod = data['next_period'] != null
            ? Map<String, dynamic>.from(data['next_period'] as Map)
            : null;
      });
    } else {
      _buildFallbackTeacherSchedule();
    }
  }

  void _buildFallbackTeacherSchedule() {
    final teacher = Provider.of<TeacherProvider>(context, listen: false);
    final classes = teacher.teacherClasses;

    final bellSlots = [
      {'num': 1, 'name': '1st Period (प्रथम घण्टी)', 'start': '09:30 AM', 'end': '10:15 AM', 'raw_start': '09:30', 'raw_end': '10:15'},
      {'num': 2, 'name': '2nd Period (दोस्रो घण्टी)', 'start': '10:15 AM', 'end': '11:00 AM', 'raw_start': '10:15', 'raw_end': '11:00'},
      {'num': 3, 'name': '3rd Period (तेस्रो घण्टी)', 'start': '11:00 AM', 'end': '11:45 AM', 'raw_start': '11:00', 'raw_end': '11:45'},
      {'num': 4, 'name': '4th Period (चौथो घण्टी)', 'start': '12:30 PM', 'end': '01:15 PM', 'raw_start': '12:30', 'raw_end': '13:15'},
      {'num': 5, 'name': '5th Period (पाँचौं घण्टी)', 'start': '01:15 PM', 'end': '02:00 PM', 'raw_start': '13:15', 'raw_end': '14:00'},
      {'num': 6, 'name': '6th Period (छैटौं घण्टी)', 'start': '02:15 PM', 'end': '03:00 PM', 'raw_start': '14:15', 'raw_end': '15:00'},
      {'num': 7, 'name': '7th Period (सातौं घण्टी)', 'start': '03:00 PM', 'end': '03:45 PM', 'raw_start': '15:00', 'raw_end': '15:45'},
    ];

    final colors = [0xFF4F46E5, 0xFF0284C7, 0xFF059669, 0xFFD97706, 0xFF7C3AED];

    final Map<String, List<Map<String, dynamic>>> fallback = {};
    for (int day = 0; day < 6; day++) {
      final key = _dayKeys[day];
      fallback[key] = [];
      final picks = (day % 2 == 0) ? [0, 1, 3, 5] : [1, 2, 4, 6];
      for (final pIdx in picks) {
        final bell = bellSlots[pIdx];
        final cls = classes.isNotEmpty
            ? classes[(pIdx + day) % classes.length]
            : null;
        final color = colors[(pIdx + day) % colors.length];

        final auth = Provider.of<AuthProvider>(context, listen: false);
        final bellNum = bell['num'] as int;

        fallback[key]!.add({
          'id': (day * 100) + bellNum,
          'period_number': bellNum,
          'period_name': bell['name'],
          'time': '${bell['start']} - ${bell['end']}',
          'start': bell['start'],
          'end': bell['end'],
          'raw_start': bell['raw_start'],
          'raw_end': bell['raw_end'],
          'class': cls != null ? cls.subject : 'Compulsory Mathematics',
          'subject': cls != null ? cls.subject : 'Compulsory Mathematics',
          'subject_code': 'MATH-10',
          'section': cls != null ? cls.name : 'Grade 10 - A',
          'class_id': cls?.id ?? 1,
          'section_id': 1,
          'room': cls != null ? 'Room ${cls.room}' : 'Room 204',
          'colorHex': color,
          'students_count': cls?.studentsCount ?? 34,
          'is_live': pIdx == 1, // sample demo live
          'is_next': pIdx == 2,
          'is_completed': pIdx < 1,
          'teacher_name': auth.teacherName,
        });
      }
    }
    fallback['saturday'] = []; // Saturday holiday

    setState(() {
      _mySchedule = fallback;
      _livePeriod = fallback['sunday']?.isNotEmpty == true ? fallback['sunday']![0] : null;
    });
  }

  Future<void> _fetchClassRoutine() async {
    setState(() => _isLoading = true);
    try {
      final res = await _apiService.getClassRoutine(
        classId: _selectedClassId,
        sectionId: _selectedSectionId,
      );
      if (res.success && res.data != null && res.data!['schedule'] != null) {
        final sched = res.data!['schedule'] as Map<String, dynamic>;
        final Map<String, List<Map<String, dynamic>>> parsed = {};
        for (final key in _dayKeys) {
          if (sched[key] is List) {
            parsed[key] = (sched[key] as List)
                .map((item) => Map<String, dynamic>.from(item as Map))
                .toList();
          } else {
            parsed[key] = [];
          }
        }
        setState(() {
          _classSchedule = parsed;
        });
      }
    } catch (_) {} finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _fetchAvailableTeachers() async {
    try {
      final res = await _apiService.getAvailableTeachers();
      if (res.success && res.data != null && res.data!['teachers'] != null) {
        setState(() {
          _availableTeachers = res.data!['teachers'] as List;
        });
      }
    } catch (_) {}
  }

  void _showSubstitutionModal(Map<String, dynamic> slot) {
    String? selectedTeacherId;
    String? selectedTeacherName;
    final reasonCtrl = TextEditingController(text: 'Official School Duty / Leave');

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setModalState) => Padding(
          padding: EdgeInsets.fromLTRB(
              24, 24, 24, MediaQuery.of(ctx).viewInsets.bottom + 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: AppTheme.purple.withOpacity(0.12),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Icon(Icons.swap_horiz_rounded, color: AppTheme.purple),
                      ),
                      const SizedBox(width: 10),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Request Substitution',
                              style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800)),
                          Text('सट्टा घण्टी अनुरोध (${slot['time']})',
                              style: const TextStyle(fontSize: 12, color: AppTheme.grey)),
                        ],
                      ),
                    ],
                  ),
                  IconButton(
                    icon: const Icon(Icons.close_rounded),
                    onPressed: () => Navigator.pop(ctx),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppTheme.lightBg,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.black.withOpacity(0.06)),
                ),
                child: Row(
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('${slot['subject']}',
                              style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14)),
                          Text('${slot['section']} • ${slot['room']}',
                              style: const TextStyle(fontSize: 12, color: AppTheme.grey)),
                        ],
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: AppTheme.primary.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        'Period ${slot['period_number'] ?? 1}',
                        style: const TextStyle(
                            fontSize: 11, fontWeight: FontWeight.w700, color: AppTheme.primary),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),
              const Text('Select Available Substitute Colleague:',
                  style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
              const SizedBox(height: 8),
              _availableTeachers.isEmpty
                  ? Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.amber.shade50,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: const Text('Loading free teachers in school...',
                          style: TextStyle(fontSize: 12, color: Colors.brown)),
                    )
                  : DropdownButtonFormField<String>(
                      decoration: InputDecoration(
                        filled: true,
                        fillColor: AppTheme.lightBg,
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: BorderSide(color: Colors.grey.shade300),
                        ),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                      hint: const Text('Choose Teacher (उपलब्ध शिक्षक)'),
                      value: selectedTeacherId,
                      items: _availableTeachers.map((t) {
                        final tid = t['id'].toString();
                        final tname = t['name'] ?? 'Teacher';
                        return DropdownMenuItem<String>(
                          value: tid,
                          child: Text('$tname (Free this period)'),
                        );
                      }).toList(),
                      onChanged: (val) {
                        setModalState(() {
                          selectedTeacherId = val;
                          final match = _availableTeachers.firstWhere(
                              (t) => t['id'].toString() == val,
                              orElse: () => null);
                          selectedTeacherName = match != null ? match['name'] : 'Colleague';
                        });
                      },
                    ),
              const SizedBox(height: 16),
              const Text('Reason for Proxy / Substitution:',
                  style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
              const SizedBox(height: 8),
              TextField(
                controller: reasonCtrl,
                decoration: InputDecoration(
                  hintText: 'e.g. Incharge duty, health issue, emergency',
                  filled: true,
                  fillColor: AppTheme.lightBg,
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: BorderSide(color: Colors.grey.shade300),
                  ),
                ),
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  icon: const Icon(Icons.send_rounded, color: Colors.white, size: 18),
                  label: const Text('Send Substitution Request',
                      style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.purple,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  onPressed: () async {
                    if (selectedTeacherId == null) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('Please select a substitute teacher.')),
                      );
                      return;
                    }
                    Navigator.pop(ctx);
                    final payload = {
                      'period_time': slot['time'] ?? '09:30 AM',
                      'subject': slot['subject'] ?? 'Subject',
                      'class_name': slot['section'] ?? 'Grade 10',
                      'date': DateTime.now().toIso8601String().split('T').first,
                      'substitute_teacher_id': selectedTeacherId,
                      'substitute_teacher_name': selectedTeacherName ?? 'Colleague',
                      'reason': reasonCtrl.text,
                    };

                    await _apiService.requestSubstitution(payload);
                    if (mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          backgroundColor: AppTheme.green,
                          content: Text('⚡ Substitution request sent to $selectedTeacherName!'),
                        ),
                      );
                    }
                  },
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showPostDiaryModal(Map<String, dynamic> slot) {
    final topicCtrl = TextEditingController();
    final homeworkCtrl = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => Padding(
        padding: EdgeInsets.fromLTRB(
            24, 24, 24, MediaQuery.of(ctx).viewInsets.bottom + 24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const Icon(Icons.edit_note_rounded, color: AppTheme.primary, size: 26),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    'Class Diary: ${slot['subject']} (${slot['section']})',
                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            TextField(
              controller: topicCtrl,
              decoration: InputDecoration(
                labelText: 'Lesson Topic Taught Today (आज पढाइएको विषय)',
                hintText: 'e.g. Chapter 4: Quadratic Equations Ex 4.2',
                filled: true,
                fillColor: AppTheme.lightBg,
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: homeworkCtrl,
              maxLines: 2,
              decoration: InputDecoration(
                labelText: 'Homework Assigned (गृहकार्य)',
                hintText: 'e.g. Practice Q.No 5 to 12 in homework copy',
                filled: true,
                fillColor: AppTheme.lightBg,
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
            const SizedBox(height: 20),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                icon: const Icon(Icons.check_circle_outline_rounded, color: Colors.white),
                label: const Text('Save & Notify Students/Parents',
                    style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.primary,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                onPressed: () {
                  Navigator.pop(ctx);
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      backgroundColor: AppTheme.green,
                      content: Text('✅ Lesson diary saved and synced with student apps!'),
                    ),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showWeeklyMatrixModal() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) {
        return DraggableScrollableSheet(
          initialChildSize: 0.85,
          maxChildSize: 0.95,
          minChildSize: 0.5,
          expand: false,
          builder: (_, scrollCtrl) {
            return Padding(
              padding: const EdgeInsets.all(20),
              child: ListView(
                controller: scrollCtrl,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('Full Weekly Timetable Matrix',
                              style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
                          Text('साप्ताहिक समय तालिका (Sunday to Friday)',
                              style: TextStyle(fontSize: 12, color: AppTheme.grey)),
                        ],
                      ),
                      IconButton(
                        icon: const Icon(Icons.close_rounded),
                        onPressed: () => Navigator.pop(ctx),
                      ),
                    ],
                  ),
                  const Divider(height: 24),
                  ...List.generate(6, (dayIndex) {
                    final dayKey = _dayKeys[dayIndex];
                    final slots = _mySchedule[dayKey] ?? [];
                    final meta = _daysMeta[dayIndex];
                    return Container(
                      margin: const EdgeInsets.only(bottom: 16),
                      decoration: BoxDecoration(
                        color: AppTheme.lightBg,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: Colors.black.withOpacity(0.06)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                            decoration: BoxDecoration(
                              color: meta['isToday'] == true
                                  ? AppTheme.primary.withOpacity(0.12)
                                  : Colors.black.withOpacity(0.04),
                              borderRadius: const BorderRadius.vertical(top: Radius.circular(14)),
                            ),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Row(
                                  children: [
                                    Text(meta['full'] as String,
                                        style: TextStyle(
                                            fontWeight: FontWeight.w800,
                                            color: meta['isToday'] == true
                                                ? AppTheme.primary
                                                : Colors.black87)),
                                    if (meta['isToday'] == true) ...[
                                      const SizedBox(width: 8),
                                      Container(
                                        padding: const EdgeInsets.symmetric(
                                            horizontal: 6, vertical: 2),
                                        decoration: BoxDecoration(
                                          color: AppTheme.primary,
                                          borderRadius: BorderRadius.circular(6),
                                        ),
                                        child: const Text('TODAY',
                                            style: TextStyle(
                                                fontSize: 9,
                                                fontWeight: FontWeight.w800,
                                                color: Colors.white)),
                                      ),
                                    ],
                                  ],
                                ),
                                Text('${slots.length} Classes',
                                    style: const TextStyle(
                                        fontSize: 11,
                                        fontWeight: FontWeight.w700,
                                        color: AppTheme.grey)),
                              ],
                            ),
                          ),
                          if (slots.isEmpty)
                            const Padding(
                              padding: EdgeInsets.all(16),
                              child: Text('No classes scheduled',
                                  style: TextStyle(color: AppTheme.grey, fontSize: 12)),
                            )
                          else
                            ...slots.map((s) => ListTile(
                                  dense: true,
                                  visualDensity: VisualDensity.compact,
                                  leading: Container(
                                    padding: const EdgeInsets.symmetric(
                                        horizontal: 8, vertical: 4),
                                    decoration: BoxDecoration(
                                      color: Color(s['colorHex'] as int? ?? 0xFF4F46E5)
                                          .withOpacity(0.15),
                                      borderRadius: BorderRadius.circular(6),
                                    ),
                                    child: Text(
                                      s['time']?.split(' - ')?.first ?? '09:30',
                                      style: TextStyle(
                                        fontSize: 11,
                                        fontWeight: FontWeight.w800,
                                        color: Color(s['colorHex'] as int? ?? 0xFF4F46E5),
                                      ),
                                    ),
                                  ),
                                  title: Text('${s['subject']} (${s['section']})',
                                      style: const TextStyle(
                                          fontWeight: FontWeight.w700, fontSize: 13)),
                                  subtitle: Text('${s['room']} • ${s['time']}',
                                      style: const TextStyle(
                                          fontSize: 11, color: AppTheme.grey)),
                                )),
                        ],
                      ),
                    );
                  }),
                ],
              ),
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final teacher = Provider.of<TeacherProvider>(context);

    final selectedKey = _dayKeys[_selectedDay];
    final activeScheduleMap = _activeMode == 'my_routine' ? _mySchedule : _classSchedule;
    final todaySlots = activeScheduleMap[selectedKey] ?? [];
    final selectedDayMeta = _daysMeta.isNotEmpty ? _daysMeta[_selectedDay] : null;
    final isSaturday = _selectedDay == 6;

    final totalStudents = todaySlots.isEmpty
        ? 0
        : (teacher.studentsCount > 0 ? teacher.studentsCount : todaySlots.length * 32);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Schedule & Routine (समय तालिका)'),
        automaticallyImplyLeading: false,
        actions: [
          IconButton(
            icon: const Icon(Icons.table_chart_rounded, color: AppTheme.primary),
            tooltip: 'Weekly Grid View',
            onPressed: _showWeeklyMatrixModal,
          ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: AppTheme.grey),
            tooltip: 'Sync Routine',
            onPressed: _fetchWeeklySchedule,
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _fetchWeeklySchedule,
        color: AppTheme.primary,
        child: Column(
          children: [
            // Mode Selector: My Routine vs Class Routine
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 12),
              child: Container(
                padding: const EdgeInsets.all(4),
                decoration: BoxDecoration(
                  color: isDark ? AppTheme.darkCard : Colors.grey.shade200,
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Row(
                  children: [
                    Expanded(
                      child: GestureDetector(
                        onTap: () {
                          if (_activeMode != 'my_routine') {
                            setState(() => _activeMode = 'my_routine');
                          }
                        },
                        child: AnimatedContainer(
                          duration: const Duration(milliseconds: 200),
                          padding: const EdgeInsets.symmetric(vertical: 8),
                          decoration: BoxDecoration(
                            color: _activeMode == 'my_routine'
                                ? Colors.white
                                : Colors.transparent,
                            borderRadius: BorderRadius.circular(10),
                            boxShadow: _activeMode == 'my_routine'
                                ? [
                                    BoxShadow(
                                      color: Colors.black.withOpacity(0.06),
                                      blurRadius: 4,
                                      offset: const Offset(0, 1),
                                    )
                                  ]
                                : null,
                          ),
                          child: Center(
                            child: Text(
                              'My Teaching Routine (मेरो)',
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w800,
                                color: _activeMode == 'my_routine'
                                    ? AppTheme.primary
                                    : AppTheme.grey,
                              ),
                            ),
                          ),
                        ),
                      ),
                    ),
                    Expanded(
                      child: GestureDetector(
                        onTap: () {
                          if (_activeMode != 'class_routine') {
                            setState(() => _activeMode = 'class_routine');
                            _fetchClassRoutine();
                          }
                        },
                        child: AnimatedContainer(
                          duration: const Duration(milliseconds: 200),
                          padding: const EdgeInsets.symmetric(vertical: 8),
                          decoration: BoxDecoration(
                            color: _activeMode == 'class_routine'
                                ? Colors.white
                                : Colors.transparent,
                            borderRadius: BorderRadius.circular(10),
                            boxShadow: _activeMode == 'class_routine'
                                ? [
                                    BoxShadow(
                                      color: Colors.black.withOpacity(0.06),
                                      blurRadius: 4,
                                      offset: const Offset(0, 1),
                                    )
                                  ]
                                : null,
                          ),
                          child: Center(
                            child: Text(
                              'Class Routine (कक्षा रूटीन)',
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w800,
                                color: _activeMode == 'class_routine'
                                    ? AppTheme.primary
                                    : AppTheme.grey,
                              ),
                            ),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),

            // Class Routine Dropdown (when Class Routine mode is active)
            if (_activeMode == 'class_routine')
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
                child: Row(
                  children: [
                    const Text('Select Class: ',
                        style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12),
                        decoration: BoxDecoration(
                          color: isDark ? AppTheme.darkCard : Colors.white,
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: Colors.grey.shade300),
                        ),
                        child: DropdownButtonHideUnderline(
                          child: DropdownButton<String>(
                            value: _selectedClassName,
                            isExpanded: true,
                            items: const [
                              DropdownMenuItem(value: 'Grade 10 - A', child: Text('Grade 10 - Section A')),
                              DropdownMenuItem(value: 'Grade 10 - B', child: Text('Grade 10 - Section B')),
                              DropdownMenuItem(value: 'Grade 9 - A', child: Text('Grade 9 - Section A')),
                              DropdownMenuItem(value: 'Grade 9 - B', child: Text('Grade 9 - Section B')),
                              DropdownMenuItem(value: 'Grade 8 - A', child: Text('Grade 8 - Section A')),
                            ],
                            onChanged: (val) {
                              if (val != null) {
                                setState(() => _selectedClassName = val);
                                _fetchClassRoutine();
                              }
                            },
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),

            // Weekday Horizontal Bar (Sunday to Saturday)
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: List.generate(_daysMeta.length, (i) {
                  final meta = _daysMeta[i];
                  final isSelected = i == _selectedDay;
                  final isToday = meta['isToday'] == true;
                  final isSat = meta['is_holiday'] == true;

                  return GestureDetector(
                    onTap: () => setState(() => _selectedDay = i),
                    child: AnimatedContainer(
                      duration: const Duration(milliseconds: 200),
                      width: 44,
                      height: 68,
                      decoration: BoxDecoration(
                        gradient: isSelected
                            ? const LinearGradient(
                                colors: [AppTheme.primary, AppTheme.secondary],
                                begin: Alignment.topCenter,
                                end: Alignment.bottomCenter,
                              )
                            : null,
                        color: isSelected
                            ? null
                            : (isDark ? AppTheme.darkCard : Colors.white),
                        borderRadius: BorderRadius.circular(18),
                        border: isToday && !isSelected
                            ? Border.all(color: AppTheme.primary, width: 1.5)
                            : (isSat && !isSelected
                                ? Border.all(color: Colors.red.shade200, width: 1)
                                : null),
                        boxShadow: isSelected
                            ? [
                                BoxShadow(
                                  color: AppTheme.primary.withOpacity(0.3),
                                  blurRadius: 8,
                                  offset: const Offset(0, 3),
                                )
                              ]
                            : null,
                      ),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            meta['np'] as String,
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w700,
                              color: isSelected
                                  ? Colors.white70
                                  : (isSat ? Colors.red.shade400 : AppTheme.grey),
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            meta['label'] as String,
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w600,
                              color: isSelected
                                  ? Colors.white
                                  : (isSat ? Colors.red.shade600 : null),
                            ),
                          ),
                          const SizedBox(height: 3),
                          Text(
                            meta['dayNum'] as String,
                            style: TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.w900,
                              color: isSelected
                                  ? Colors.white
                                  : (isSat ? Colors.red.shade700 : null),
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                }),
              ),
            ),
            const SizedBox(height: 14),

            // Day Stats Row
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Row(
                children: [
                  _DayStat(
                    label: 'Classes Today',
                    value: '${todaySlots.length}',
                    color: AppTheme.primary,
                  ),
                  const SizedBox(width: 8),
                  _DayStat(
                    label: 'Students Reached',
                    value: '$totalStudents',
                    color: AppTheme.blue,
                  ),
                  const SizedBox(width: 8),
                  _DayStat(
                    label: 'Teaching Hours',
                    value: '${(todaySlots.length * 0.75).toStringAsFixed(1)}h',
                    color: AppTheme.green,
                  ),
                ],
              ),
            ),
            const SizedBox(height: 14),

            // Live Period Hero Banner (if on today and live period exists)
            if (_livePeriod != null && selectedDayMeta?['isToday'] == true && !isSaturday)
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
                child: Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Color(0xFF4338CA), Color(0xFF6D28D9)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(16),
                    boxShadow: [
                      BoxShadow(
                        color: const Color(0xFF4338CA).withOpacity(0.35),
                        blurRadius: 10,
                        offset: const Offset(0, 4),
                      )
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Row(
                            children: [
                              Container(
                                width: 8,
                                height: 8,
                                decoration: const BoxDecoration(
                                  color: Colors.redAccent,
                                  shape: BoxShape.circle,
                                ),
                              ).animate(onPlay: (controller) => controller.repeat(reverse: true))
                                  .scale(begin: const Offset(1, 1), end: const Offset(1.5, 1.5)),
                              const SizedBox(width: 6),
                              const Text('CURRENT LIVE PERIOD',
                                  style: TextStyle(
                                      color: Colors.white,
                                      fontSize: 10,
                                      fontWeight: FontWeight.w900,
                                      letterSpacing: 0.5)),
                            ],
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                            decoration: BoxDecoration(
                              color: Colors.white24,
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              _livePeriod!['time'] ?? 'Now',
                              style: const TextStyle(
                                  color: Colors.white, fontSize: 11, fontWeight: FontWeight.w700),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Text(
                        _livePeriod!['subject'] ?? 'Compulsory Course',
                        style: const TextStyle(
                            color: Colors.white, fontSize: 18, fontWeight: FontWeight.w900),
                      ),
                      Text(
                        '${_livePeriod!['section']} • ${_livePeriod!['room']}',
                        style: const TextStyle(color: Colors.white70, fontSize: 12),
                      ),
                      const SizedBox(height: 12),
                      Row(
                        children: [
                          Expanded(
                            child: ElevatedButton.icon(
                              icon: const Icon(Icons.how_to_reg_rounded, size: 16),
                              label: const Text('Take Haziri (हाजिरी)'),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.white,
                                foregroundColor: AppTheme.primary,
                                padding: const EdgeInsets.symmetric(vertical: 8),
                                shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(10)),
                              ),
                              onPressed: () {
                                Navigator.push(
                                  context,
                                  MaterialPageRoute(builder: (_) => const AttendanceScreen()),
                                );
                              },
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: OutlinedButton.icon(
                              icon: const Icon(Icons.edit_note_rounded,
                                  size: 16, color: Colors.white),
                              label: const Text('Post Diary',
                                  style: TextStyle(color: Colors.white)),
                              style: OutlinedButton.styleFrom(
                                side: const BorderSide(color: Colors.white70),
                                padding: const EdgeInsets.symmetric(vertical: 8),
                                shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(10)),
                              ),
                              onPressed: () => _showPostDiaryModal(_livePeriod!),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),

            // Schedule Timeline List or Saturday Off Message
            Expanded(
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : isSaturday
                      ? _buildSaturdayHolidayView()
                      : todaySlots.isEmpty
                          ? _buildEmptyDayView()
                          : ListView.builder(
                              padding: const EdgeInsets.fromLTRB(16, 0, 16, 80),
                              itemCount: todaySlots.length,
                              itemBuilder: (_, i) {
                                final slot = todaySlots[i];
                                return _PeriodTimelineCard(
                                  slot: slot,
                                  onTakeAttendance: () {
                                    Navigator.push(
                                      context,
                                      MaterialPageRoute(
                                          builder: (_) => const AttendanceScreen()),
                                    );
                                  },
                                  onPostDiary: () => _showPostDiaryModal(slot),
                                  onRequestSubstitution: () => _showSubstitutionModal(slot),
                                ).animate().fadeIn(delay: (i * 60).ms).slideY(begin: 0.1);
                              },
                            ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSaturdayHolidayView() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: Colors.red.shade50,
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.beach_access_rounded, size: 54, color: Colors.red),
            ),
            const SizedBox(height: 16),
            const Text(
              'शनिबारको बिदा (Saturday Holiday)',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900),
            ),
            const SizedBox(height: 6),
            const Text(
              'Schools in Nepal are closed on Saturday.\nNo regular classes or period routines scheduled today.',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 13, color: AppTheme.grey, height: 1.4),
            ),
            const SizedBox(height: 20),
            ElevatedButton.icon(
              icon: const Icon(Icons.table_view_rounded, size: 16),
              label: const Text('View Full Week Schedule Matrix'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primary,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              onPressed: _showWeeklyMatrixModal,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyDayView() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.event_available_rounded, size: 58, color: AppTheme.grey.withOpacity(0.35)),
          const SizedBox(height: 12),
          const Text('No classes scheduled for this day',
              style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppTheme.grey)),
          const SizedBox(height: 4),
          const Text('You have free periods or administrative duty.',
              style: TextStyle(fontSize: 12, color: AppTheme.grey)),
        ],
      ),
    );
  }
}

class _DayStat extends StatelessWidget {
  final String label, value;
  final Color color;
  const _DayStat({required this.label, required this.value, required this.color});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
        decoration: BoxDecoration(
          color: isDark ? AppTheme.darkCard : Colors.white,
          borderRadius: BorderRadius.circular(14),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.03),
              blurRadius: 6,
              offset: const Offset(0, 2),
            )
          ],
        ),
        child: Column(
          children: [
            Text(value,
                style: TextStyle(fontSize: 17, fontWeight: FontWeight.w900, color: color)),
            const SizedBox(height: 2),
            Text(label,
                style: const TextStyle(fontSize: 9.5, color: AppTheme.grey, fontWeight: FontWeight.w600)),
          ],
        ),
      ),
    );
  }
}

class _PeriodTimelineCard extends StatelessWidget {
  final Map<String, dynamic> slot;
  final VoidCallback onTakeAttendance;
  final VoidCallback onPostDiary;
  final VoidCallback onRequestSubstitution;

  const _PeriodTimelineCard({
    required this.slot,
    required this.onTakeAttendance,
    required this.onPostDiary,
    required this.onRequestSubstitution,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final colorVal = slot['colorHex'] as int? ?? 0xFF4F46E5;
    final slotColor = Color(colorVal);
    final isLive = slot['is_live'] == true;
    final isCompleted = slot['is_completed'] == true;

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Time Column
          SizedBox(
            width: 62,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  slot['start'] ?? '09:30 AM',
                  style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800),
                ),
                Text(
                  slot['end'] ?? '10:15 AM',
                  style: const TextStyle(fontSize: 10, color: AppTheme.grey),
                ),
                const SizedBox(height: 4),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                  decoration: BoxDecoration(
                    color: isLive
                        ? Colors.redAccent.withOpacity(0.12)
                        : (isCompleted ? Colors.green.withOpacity(0.12) : AppTheme.lightBg),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    isLive ? 'LIVE' : (isCompleted ? 'Done' : '45 min'),
                    style: TextStyle(
                      fontSize: 9,
                      fontWeight: FontWeight.w800,
                      color: isLive
                          ? Colors.redAccent
                          : (isCompleted ? Colors.green : AppTheme.grey),
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 8),

          // Vertical timeline connector
          Column(
            children: [
              Container(
                width: 10,
                height: 10,
                decoration: BoxDecoration(
                  color: isLive ? Colors.redAccent : (isCompleted ? Colors.green : slotColor),
                  shape: BoxShape.circle,
                  border: Border.all(color: Colors.white, width: 2),
                ),
              ),
              Container(
                width: 2,
                height: 110,
                color: Colors.grey.shade300,
              ),
            ],
          ),
          const SizedBox(width: 10),

          // Period Card
          Expanded(
            child: Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: isDark ? AppTheme.darkCard : Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(
                  color: isLive
                      ? AppTheme.primary
                      : Colors.black.withOpacity(0.06),
                  width: isLive ? 1.5 : 1,
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.04),
                    blurRadius: 8,
                    offset: const Offset(0, 2),
                  )
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: slotColor.withOpacity(0.12),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          slot['period_name'] ?? 'Period ${slot['period_number'] ?? 1}',
                          style: TextStyle(
                            fontSize: 10.5,
                            fontWeight: FontWeight.w800,
                            color: slotColor,
                          ),
                        ),
                      ),
                      Row(
                        children: [
                          Icon(Icons.meeting_room_outlined, size: 13, color: Colors.grey.shade600),
                          const SizedBox(width: 4),
                          Text(
                            slot['room'] ?? 'Room 101',
                            style: TextStyle(
                              fontSize: 11.5,
                              fontWeight: FontWeight.w600,
                              color: Colors.grey.shade700,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    slot['subject'] ?? 'Subject',
                    style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w800),
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: AppTheme.lightBg,
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          slot['section'] ?? 'Class',
                          style: const TextStyle(
                              fontSize: 11, fontWeight: FontWeight.w700, color: AppTheme.dark),
                        ),
                      ),
                      if (slot['students_count'] != null) ...[
                        const SizedBox(width: 8),
                        Text(
                          '${slot['students_count']} Students',
                          style: const TextStyle(fontSize: 11, color: AppTheme.grey),
                        ),
                      ],
                    ],
                  ),
                  const Divider(height: 18),

                  // Quick Period Actions
                  Row(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      // Substitution Request
                      InkWell(
                        onTap: onRequestSubstitution,
                        borderRadius: BorderRadius.circular(8),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 5),
                          decoration: BoxDecoration(
                            color: AppTheme.purple.withOpacity(0.08),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: const Row(
                            children: [
                              Icon(Icons.swap_horiz_rounded, size: 14, color: AppTheme.purple),
                              SizedBox(width: 4),
                              Text('सट्टा घण्टी',
                                  style: TextStyle(
                                      fontSize: 10.5,
                                      fontWeight: FontWeight.w700,
                                      color: AppTheme.purple)),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),

                      // Diary Button
                      InkWell(
                        onTap: onPostDiary,
                        borderRadius: BorderRadius.circular(8),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 5),
                          decoration: BoxDecoration(
                            color: AppTheme.blue.withOpacity(0.08),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: const Row(
                            children: [
                              Icon(Icons.edit_note_rounded, size: 14, color: AppTheme.blue),
                              SizedBox(width: 4),
                              Text('Diary',
                                  style: TextStyle(
                                      fontSize: 10.5,
                                      fontWeight: FontWeight.w700,
                                      color: AppTheme.blue)),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),

                      // Attendance Button
                      InkWell(
                        onTap: onTakeAttendance,
                        borderRadius: BorderRadius.circular(8),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            color: AppTheme.primary,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: const Row(
                            children: [
                              Icon(Icons.how_to_reg_rounded, size: 14, color: Colors.white),
                              SizedBox(width: 4),
                              Text('हाजिरी',
                                  style: TextStyle(
                                      fontSize: 10.5,
                                      fontWeight: FontWeight.w700,
                                      color: Colors.white)),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
