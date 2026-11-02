import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../theme/app_theme.dart';
import '../widgets/common_widgets.dart';
import '../providers/teacher_provider.dart';
import '../services/api_service.dart';
import 'assignments_screen.dart';

class LessonPlannerScreen extends StatefulWidget {
  const LessonPlannerScreen({super.key});
  @override
  State<LessonPlannerScreen> createState() => _LessonPlannerScreenState();
}

class _LessonPlannerScreenState extends State<LessonPlannerScreen> {
  final ApiService _apiService = ApiService();
  bool _isLoading = false;
  int _selectedDay = 0; // 0=Sun ... 5=Fri

  List<Map<String, dynamic>> _daysMeta = [];
  List<Map<String, dynamic>> _plans = [];

  @override
  void initState() {
    super.initState();
    _initDaysMeta();
    _loadCachedPlans();
    _fetchLessonPlans();
  }

  void _initDaysMeta() {
    final now = DateTime.now();
    // Dart: Monday=1 ... Sunday=7. In Nepal, week starts Sunday (index 0)
    final currentDayIndex = now.weekday == 7 ? 0 : now.weekday;
    final startOfSun = now.subtract(Duration(days: currentDayIndex));

    final dayConfigs = [
      {'label': 'Sun (आइत)', 'np': 'आइत'},
      {'label': 'Mon (सोम)', 'np': 'सोम'},
      {'label': 'Tue (मङ्गल)', 'np': 'मङ्गल'},
      {'label': 'Wed (बुध)', 'np': 'बुध'},
      {'label': 'Thu (बिही)', 'np': 'बिही'},
      {'label': 'Fri (शुक्र)', 'np': 'शुक्र'},
    ];

    final nepaliNumerals = ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'];

    _daysMeta = List.generate(6, (i) {
      final date = startOfSun.add(Duration(days: i));
      final dayStr = date.day.toString().padLeft(2, '0');
      final nepaliNum = dayStr.split('').map((ch) {
        final d = int.tryParse(ch);
        return d != null ? nepaliNumerals[d] : ch;
      }).join();

      return {
        'index': i,
        'label': dayConfigs[i]['label'] as String,
        'np': dayConfigs[i]['np'] as String,
        'num': nepaliNum,
        'dayNum': '${date.day}',
        'date': date.toIso8601String().split('T').first,
        'isToday': i == (currentDayIndex < 6 ? currentDayIndex : 0),
      };
    });

    _selectedDay = currentDayIndex < 6 ? currentDayIndex : 0;
  }

  Future<void> _loadCachedPlans() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final cached = prefs.getString('cached_lesson_plans');
      if (cached != null) {
        final list = jsonDecode(cached) as List;
        setState(() {
          _plans = list.map((e) => Map<String, dynamic>.from(e as Map)).toList();
        });
      }
    } catch (_) {}
  }

  Future<void> _fetchLessonPlans() async {
    setState(() => _isLoading = true);
    try {
      final selectedDate = _daysMeta.isNotEmpty ? _daysMeta[_selectedDay]['date'] as String : null;
      final res = await _apiService.getLessonPlans(date: selectedDate);
      if (res.success && res.data != null) {
        final list = res.data!;
        setState(() {
          _plans = list.map((e) => Map<String, dynamic>.from(e as Map)).toList();
        });
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('cached_lesson_plans', jsonEncode(_plans));
      } else if (_plans.isEmpty) {
        _buildFallbackPlans();
      }
    } catch (_) {
      if (_plans.isEmpty) _buildFallbackPlans();
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _buildFallbackPlans() {
    setState(() {
      _plans = [
        {
          'id': 1,
          'title': 'Quadratic Equations & Roots (वर्ग समीकरण र मूलहरू)',
          'class': 'Compulsory Mathematics',
          'subject': 'Compulsory Mathematics',
          'section': 'Grade 10 - A (Mt. Everest)',
          'time': '09:30 - 10:15 AM (Period 1)',
          'duration': '45 mins',
          'colorHex': 0xFF0F4C81,
          'objective':
              'Students will solve quadratic equations by completing the square method and derive the quadratic formula (-b ± √(b²-4ac))/2a.',
          'materials': [
            'CDC Nepal Math Book Ch.5',
            'Smart Interactive Board',
            'Casio Scientific Calculator fx-991ES',
            'Graph Notebook'
          ],
          'teaching_methods': 'Problem-Based Inquiry & Guided Derivation',
          'activities': [
            {'name': 'Mental Math Warmup: Quick quadratic roots & factoring', 'duration': '5 min'},
            {'name': 'Derivation of Quadratic Formula step-by-step on smartboard', 'duration': '15 min'},
            {'name': 'Worked Example: Non-factorable equation 2x² - 7x + 3 = 0', 'duration': '10 min'},
            {'name': 'Paired Student Activity: Textbook Exercise 5.2 Q.3', 'duration': '10 min'},
            {'name': 'Exit Slip Evaluation & Concept Synthesis', 'duration': '5 min'},
          ],
          'homework': 'CDC Textbook Page 142, Q.No 4 (a to f) in homework copy.',
          'status': 'ready',
        },
        {
          'id': 2,
          'title': 'Compound Multiple Angle Identities (sin 2A, cos 2A, tan 2A)',
          'class': 'Optional Mathematics',
          'subject': 'Optional Mathematics',
          'section': 'Grade 10 - B (Annapurna)',
          'time': '10:15 - 11:00 AM (Period 2)',
          'duration': '45 mins',
          'colorHex': 0xFF2563EB,
          'objective':
              'Students will prove compound trigonometric multiple angle identities sin 2A & cos 2A and solve 4-mark SEE questions.',
          'materials': [
            'Trigonometry Formula Chart',
            'Graph Notebook',
            'GeoGebra Unit Circle Simulation'
          ],
          'teaching_methods': 'Direct Instruction & Cooperative Group Proof',
          'activities': [
            {'name': 'Review: Addition theorem sin(A+B) & cos(A+B)', 'duration': '8 min'},
            {'name': 'Algebraic and Geometric proof of multiple angle identities', 'duration': '15 min'},
            {'name': 'Group Workout: Prove cos 2A in terms of tan A', 'duration': '15 min'},
            {'name': 'Summary, Doubt Clearance & Homework Briefing', 'duration': '7 min'},
          ],
          'homework': 'Optional Math Practice Book Chapter 3 Set B (Questions 1 to 8).',
          'status': 'ready',
        },
        {
          'id': 3,
          'title': 'Cell Division: Mitosis Stages & Cytokinesis (समविभाजन)',
          'class': 'Science & Technology',
          'subject': 'Science & Technology',
          'section': 'Grade 9 - B',
          'time': '11:00 - 11:45 AM (Period 3)',
          'duration': '45 mins',
          'colorHex': 0xFF10B981,
          'objective':
              'Identify the four mitotic stages (Prophase, Metaphase, Anaphase, Telophase) and explain biological significance of chromosome duplication.',
          'materials': [
            'Compound Microscope',
            'Permanent Slide of Onion Root Tip',
            'Mitosis 3D Animation Video'
          ],
          'teaching_methods': 'Laboratory Demonstration & Visual Media',
          'activities': [
            {'name': 'Video Clip: Real-time cell division under microscope', 'duration': '7 min'},
            {'name': 'Diagrammatic explanation of Prophase & Metaphase spindle', 'duration': '18 min'},
            {'name': 'Student Microscope Station observation', 'duration': '15 min'},
            {'name': 'Recap quiz on chromosome counting', 'duration': '5 min'},
          ],
          'homework': 'Draw well-labelled four stages of Mitosis in practical record notebook.',
          'status': 'completed',
        },
        {
          'id': 4,
          'title': 'Nepal Infrastructure Projects: Fast Track & Energy Corridor',
          'class': 'Social Studies',
          'subject': 'Social Studies',
          'section': 'Grade 10 - B',
          'time': '12:30 - 01:15 PM (Period 4)',
          'duration': '45 mins',
          'colorHex': 0xFFD97706,
          'objective':
              'Analyze economic transformation brought by Kathmandu-Tarai Expressway and hydro projects in Nepal.',
          'materials': ['National Pride Projects Map', 'Case Study Handout'],
          'teaching_methods': 'Case Study Discussion & Socratic Dialogue',
          'activities': [
            {'name': 'Map study of road corridors in Nepal', 'duration': '10 min'},
            {'name': 'Presentation on travel time reduction & GDP impact', 'duration': '20 min'},
            {'name': 'Debate: Environmental impact vs Economic development', 'duration': '12 min'},
            {'name': 'Teacher summation and assignment', 'duration': '3 min'},
          ],
          'homework': 'Prepare a 150-word report on one hydropower project in your province.',
          'status': 'draft',
        },
      ];
    });
  }

  // ─────────────────────────────────────────────────────────────────────────
  // Lesson Plan Details & Dossier Modal
  // ─────────────────────────────────────────────────────────────────────────

  void _showPlanDossier(Map<String, dynamic> plan) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final planId = plan['id'].toString();
    final isCompleted = plan['status'] == 'completed';
    final isDraft = plan['status'] == 'draft';
    final activities = plan['activities'] is List ? (plan['activities'] as List) : [];
    final materials = plan['materials'] is List ? (plan['materials'] as List) : [];

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: isDark ? AppTheme.darkCard : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDossierState) => DraggableScrollableSheet(
          initialChildSize: 0.88,
          maxChildSize: 0.95,
          minChildSize: 0.5,
          expand: false,
          builder: (_, scrollCtrl) => Padding(
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
            child: ListView(
              controller: scrollCtrl,
              children: [
                // Header
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: Color(plan['colorHex'] as int? ?? 0xFF0F4C81).withOpacity(0.12),
                        borderRadius: BorderRadius.circular(14),
                      ),
                      child: Icon(
                        Icons.menu_book_rounded,
                        color: Color(plan['colorHex'] as int? ?? 0xFF0F4C81),
                        size: 26,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            plan['title']?.toString() ?? 'Lesson Plan',
                            style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            '${plan['class'] ?? plan['subject']} • ${plan['section']}',
                            style: const TextStyle(
                                fontSize: 12, fontWeight: FontWeight.w600, color: AppTheme.grey),
                          ),
                          Text(
                            '${plan['time'] ?? 'Period 45 min'}',
                            style: const TextStyle(fontSize: 11, color: AppTheme.grey),
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close_rounded),
                      onPressed: () => Navigator.pop(ctx),
                    ),
                  ],
                ),
                const Divider(height: 24),

                // Status & Actions Toolbar
                Row(
                  children: [
                    Expanded(
                      child: ElevatedButton.icon(
                        icon: Icon(
                          isCompleted
                              ? Icons.check_circle_rounded
                              : Icons.check_circle_outline_rounded,
                          size: 16,
                          color: Colors.white,
                        ),
                        label: Text(
                          isCompleted
                              ? 'Taught & Completed (सम्पन्न)'
                              : 'Mark as Completed (पाठ सम्पन्न भयो)',
                          style: const TextStyle(
                              fontSize: 12, fontWeight: FontWeight.w800, color: Colors.white),
                        ),
                        style: ElevatedButton.styleFrom(
                          backgroundColor:
                              isCompleted ? AppTheme.green : AppTheme.primary,
                          padding: const EdgeInsets.symmetric(vertical: 10),
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(10)),
                        ),
                        onPressed: () async {
                          final newStatus = isCompleted ? 'ready' : 'completed';
                          setDossierState(() {
                            plan['status'] = newStatus;
                          });
                          setState(() {
                            plan['status'] = newStatus;
                          });
                          await _apiService.markLessonPlanCompleted(planId);
                          final prefs = await SharedPreferences.getInstance();
                          await prefs.setString(
                              'cached_lesson_plans', jsonEncode(_plans));

                          if (context.mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                backgroundColor: AppTheme.green,
                                content: Text(newStatus == 'completed'
                                    ? '✅ Lesson plan marked as Taught & Completed!'
                                    : 'Lesson plan status reset to Ready.'),
                              ),
                            );
                          }
                        },
                      ),
                    ),
                    const SizedBox(width: 8),
                    IconButton(
                      icon: const Icon(Icons.delete_outline_rounded, color: Colors.red),
                      tooltip: 'Delete Plan',
                      onPressed: () {
                        Navigator.pop(ctx);
                        _confirmDeletePlan(plan);
                      },
                    ),
                  ],
                ),
                const SizedBox(height: 16),

                // Learning Objectives Section
                _buildDossierSection(
                  title: 'Learning Objectives & Competencies (सिकाइ उपलब्धि)',
                  icon: Icons.track_changes_rounded,
                  iconColor: AppTheme.primary,
                  child: Text(
                    plan['objective']?.toString() ??
                        'Students will master curriculum concepts and solve problems.',
                    style: const TextStyle(fontSize: 13, height: 1.45, fontWeight: FontWeight.w600),
                  ),
                ),
                const SizedBox(height: 14),

                // Teaching Methodology
                _buildDossierSection(
                  title: 'Teaching Methodology (शिक्षण विधि)',
                  icon: Icons.psychology_rounded,
                  iconColor: AppTheme.purple,
                  child: Text(
                    plan['teaching_methods']?.toString() ??
                        'Problem-Based Learning, Guided Discovery & Interactive Discussion',
                    style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600),
                  ),
                ),
                const SizedBox(height: 14),

                // Educational Materials & Aids
                _buildDossierSection(
                  title: 'Teaching Aids & Materials (शैक्षिक सामग्री)',
                  icon: Icons.category_rounded,
                  iconColor: AppTheme.blue,
                  child: materials.isEmpty
                      ? const Text('Standard Textbook & Whiteboard',
                          style: TextStyle(fontSize: 12.5))
                      : Wrap(
                          spacing: 6,
                          runSpacing: 6,
                          children: materials.map((m) {
                            return Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 10, vertical: 5),
                              decoration: BoxDecoration(
                                color: AppTheme.blue.withOpacity(0.08),
                                borderRadius: BorderRadius.circular(8),
                                border:
                                    Border.all(color: AppTheme.blue.withOpacity(0.2)),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(Icons.check_rounded,
                                      size: 13, color: AppTheme.blue),
                                  const SizedBox(width: 4),
                                  Text(
                                    m.toString(),
                                    style: const TextStyle(
                                        fontSize: 11.5,
                                        fontWeight: FontWeight.w700,
                                        color: AppTheme.blue),
                                  ),
                                ],
                              ),
                            );
                          }).toList(),
                        ),
                ),
                const SizedBox(height: 14),

                // Step-by-Step Activities Timeline
                _buildDossierSection(
                  title: 'Classroom Activity Breakdown (शिक्षण क्रियाकलाप)',
                  icon: Icons.access_time_filled_rounded,
                  iconColor: AppTheme.green,
                  child: Column(
                    children: activities.asMap().entries.map((entry) {
                      final idx = entry.key + 1;
                      final act = entry.value as Map;
                      return Container(
                        margin: const EdgeInsets.only(bottom: 8),
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            CircleAvatar(
                              radius: 11,
                              backgroundColor: AppTheme.green.withOpacity(0.2),
                              child: Text('$idx',
                                  style: const TextStyle(
                                      fontSize: 10,
                                      fontWeight: FontWeight.w900,
                                      color: AppTheme.green)),
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                act['name']?.toString() ?? '',
                                style: const TextStyle(
                                    fontSize: 12, fontWeight: FontWeight.w600),
                              ),
                            ),
                            const SizedBox(width: 6),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: Colors.green.shade50,
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                act['duration']?.toString() ?? '',
                                style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.w800,
                                    color: Colors.green.shade700),
                              ),
                            ),
                          ],
                        ),
                      );
                    }).toList(),
                  ),
                ),
                const SizedBox(height: 14),

                // Homework & Follow-up Box
                _buildDossierSection(
                  title: 'Homework / Follow-up Task (गृहकार्य)',
                  icon: Icons.assignment_turned_in_rounded,
                  iconColor: AppTheme.orange,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        plan['homework']?.toString() ?? 'Review today notes.',
                        style: const TextStyle(
                            fontSize: 12.5, fontWeight: FontWeight.w700),
                      ),
                      const SizedBox(height: 10),
                      ElevatedButton.icon(
                        icon: const Icon(Icons.publish_rounded, size: 15),
                        label: const Text('Post to Homework Hub',
                            style: TextStyle(
                                fontSize: 11, fontWeight: FontWeight.w800)),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppTheme.orange,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(
                              horizontal: 12, vertical: 6),
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(8)),
                        ),
                        onPressed: () {
                          Navigator.pop(ctx);
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                                builder: (_) => const AssignmentsScreen()),
                          );
                        },
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildDossierSection({
    required String title,
    required IconData icon,
    required Color iconColor,
    required Widget child,
  }) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkBg : AppTheme.lightBg,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.black.withOpacity(0.06)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, size: 16, color: iconColor),
              const SizedBox(width: 6),
              Text(
                title,
                style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w800,
                    color: Colors.grey.shade800),
              ),
            ],
          ),
          const SizedBox(height: 8),
          child,
        ],
      ),
    );
  }

  void _confirmDeletePlan(Map<String, dynamic> plan) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Delete Lesson Plan?'),
        content: Text('Remove "${plan['title']}" from your lesson planner?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
            onPressed: () async {
              Navigator.pop(ctx);
              final id = plan['id'].toString();
              setState(() {
                _plans.removeWhere((p) => p['id'].toString() == id);
              });
              await _apiService.deleteLessonPlan(id);
              final prefs = await SharedPreferences.getInstance();
              await prefs.setString('cached_lesson_plans', jsonEncode(_plans));
              if (mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Lesson plan deleted successfully.')),
                );
              }
            },
            child: const Text('Delete', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  // ─────────────────────────────────────────────────────────────────────────
  // New Lesson Plan Creation Sheet
  // ─────────────────────────────────────────────────────────────────────────

  void _showAddPlanSheet(bool isDark) {
    final teacherProvider = Provider.of<TeacherProvider>(context, listen: false);
    final classesList = teacherProvider.teacherClasses;

    final titleCtrl = TextEditingController();
    final objCtrl = TextEditingController();
    final homeworkCtrl = TextEditingController();
    final materialsCtrl = TextEditingController(text: 'CDC Textbook, Smartboard, Graph Copy');
    final methodCtrl = TextEditingController(text: 'Problem Solving & Guided Discussion');
    String selectedClass = classesList.isNotEmpty ? classesList.first.section : 'Grade 10 - A';
    String selectedSubject = classesList.isNotEmpty ? classesList.first.subject : 'Compulsory Mathematics';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: isDark ? AppTheme.darkCard : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDialogState) => Padding(
          padding: EdgeInsets.fromLTRB(
            20,
            20,
            20,
            MediaQuery.of(ctx).viewInsets.bottom + 20,
          ),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Row(
                      children: [
                        Icon(Icons.note_add_rounded, color: AppTheme.primary, size: 22),
                        SizedBox(width: 8),
                        Text(
                          'Create Lesson Plan (नयाँ पाठ योजना)',
                          style: TextStyle(fontSize: 17, fontWeight: FontWeight.w900),
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
                TextField(
                  controller: titleCtrl,
                  decoration: InputDecoration(
                    labelText: 'Topic / Chapter Title (पाठ / शीर्षक)',
                    hintText: 'e.g. Linear Programming & Feasible Region',
                    filled: true,
                    fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    Expanded(
                      child: DropdownButtonFormField<String>(
                        value: selectedClass,
                        decoration: InputDecoration(
                          labelText: 'Class',
                          filled: true,
                          fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        items: (classesList.isNotEmpty
                                ? classesList.map((c) => c.section).toSet().toList()
                                : ['Grade 10 - A', 'Grade 10 - B', 'Grade 9 - B', 'Grade 8 - A'])
                            .map((s) => DropdownMenuItem(value: s, child: Text(s)))
                            .toList(),
                        onChanged: (v) {
                          if (v != null) setDialogState(() => selectedClass = v);
                        },
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: TextField(
                        controller: TextEditingController(text: selectedSubject),
                        onChanged: (v) => selectedSubject = v,
                        decoration: InputDecoration(
                          labelText: 'Subject',
                          filled: true,
                          fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                TextField(
                  controller: objCtrl,
                  maxLines: 2,
                  decoration: InputDecoration(
                    labelText: 'Learning Objectives & Competencies (सिकाइ उपलब्धि)',
                    hintText: 'Students will solve inequalities graphically and identify vertices...',
                    filled: true,
                    fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
                const SizedBox(height: 10),
                TextField(
                  controller: materialsCtrl,
                  decoration: InputDecoration(
                    labelText: 'Teaching Materials (शैक्षिक सामग्री)',
                    hintText: 'e.g. Graph copy, Casio fx-991, Smartboard',
                    filled: true,
                    fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
                const SizedBox(height: 10),
                TextField(
                  controller: homeworkCtrl,
                  decoration: InputDecoration(
                    labelText: 'Homework Assignment (गृहकार्य)',
                    hintText: 'e.g. Exercise 4.1 Questions 1 to 6 in notebook',
                    filled: true,
                    fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
                const SizedBox(height: 18),
                SizedBox(
                  width: double.infinity,
                  height: 48,
                  child: ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppTheme.primary,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                    onPressed: () async {
                      if (titleCtrl.text.trim().isEmpty) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Please enter a lesson plan topic title.')),
                        );
                        return;
                      }

                      final newTitle = titleCtrl.text.trim();
                      final newObj = objCtrl.text.trim().isNotEmpty
                          ? objCtrl.text.trim()
                          : 'Master CDC textbook concepts and solve exercises.';
                      final newHw = homeworkCtrl.text.trim().isNotEmpty
                          ? homeworkCtrl.text.trim()
                          : 'Textbook Practice Questions';
                      final mats = materialsCtrl.text
                          .split(',')
                          .map((s) => s.trim())
                          .where((s) => s.isNotEmpty)
                          .toList();

                      final payload = {
                        'title': newTitle,
                        'subject': selectedSubject,
                        'class_section': selectedClass,
                        'objective': newObj,
                        'homework': newHw,
                        'materials': mats,
                        'teaching_methods': methodCtrl.text.trim(),
                        'duration_minutes': 45,
                        'planned_date': _daysMeta.isNotEmpty
                            ? _daysMeta[_selectedDay]['date'] as String
                            : DateTime.now().toIso8601String().split('T').first,
                        'status': 'ready',
                      };

                      Navigator.pop(ctx);

                      final res = await _apiService.createLessonPlan(payload);

                      final Map<String, dynamic> createdPlan = (res.success &&
                              res.data is Map &&
                              res.data!['lesson_plan'] != null)
                          ? Map<String, dynamic>.from(res.data!['lesson_plan'] as Map)
                          : {
                              'id': DateTime.now().millisecondsSinceEpoch,
                              'title': newTitle,
                              'class': selectedSubject,
                              'subject': selectedSubject,
                              'section': selectedClass,
                              'time': '45 mins (Scheduled)',
                              'duration': '45 mins',
                              'colorHex': 0xFF0F4C81,
                              'objective': newObj,
                              'materials': mats,
                              'activities': [
                                {'name': 'Concept introduction & motivation', 'duration': '10 min'},
                                {'name': 'Step-by-step problem demonstration', 'duration': '20 min'},
                                {'name': 'Student practice & doubt clearance', 'duration': '15 min'},
                              ],
                              'homework': newHw,
                              'status': 'ready',
                            };

                      setState(() {
                        _plans.insert(0, createdPlan);
                      });

                      final prefs = await SharedPreferences.getInstance();
                      await prefs.setString(
                          'cached_lesson_plans', jsonEncode(_plans));

                      if (mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            backgroundColor: AppTheme.green,
                            content: Text(
                                '🚀 Lesson plan saved and synced with Teacher Academic Diary!'),
                          ),
                        );
                      }
                    },
                    child: const Text('Save & Sync Lesson Plan 🚀',
                        style: TextStyle(fontWeight: FontWeight.w800)),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
      appBar: AppBar(
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Lesson Planner (पाठ योजना)',
              style: TextStyle(fontSize: 16.5, fontWeight: FontWeight.w900),
            ),
            Text(
              'Curriculum, Objectives & Activity Timeline • 2081',
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w600,
                color: AppTheme.darkSubtext,
              ),
            ),
          ],
        ),
        leading: CircleIconButton(
          icon: Icons.arrow_back_ios_new_rounded,
          onTap: () => Navigator.pop(context),
          size: 38,
        ),
        leadingWidth: 60,
        actions: [
          Container(
            margin: const EdgeInsets.only(right: 14),
            child: ElevatedButton.icon(
              onPressed: () => _showAddPlanSheet(isDark),
              icon: const Icon(Icons.note_add_rounded, size: 16),
              label: const Text(
                'New Plan',
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primary,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _fetchLessonPlans,
        color: AppTheme.primary,
        child: Column(
          children: [
            // 6-Day School Week Selector (Sunday to Friday)
            Container(
              height: 72,
              margin: const EdgeInsets.fromLTRB(16, 6, 16, 12),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: List.generate(_daysMeta.length, (i) {
                  final meta = _daysMeta[i];
                  final isSelected = i == _selectedDay;
                  final isToday = meta['isToday'] == true;

                  return Expanded(
                    child: GestureDetector(
                      onTap: () {
                        setState(() => _selectedDay = i);
                        _fetchLessonPlans();
                      },
                      child: AnimatedContainer(
                        duration: const Duration(milliseconds: 200),
                        margin: const EdgeInsets.symmetric(horizontal: 3),
                        decoration: BoxDecoration(
                          gradient: isSelected
                              ? const LinearGradient(
                                  colors: [Color(0xFF0F4C81), Color(0xFF2563EB)],
                                  begin: Alignment.topCenter,
                                  end: Alignment.bottomCenter,
                                )
                              : null,
                          color: isSelected
                              ? null
                              : (isDark ? AppTheme.darkCard : Colors.white),
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(
                            color: isSelected
                                ? AppTheme.primary
                                : (isToday
                                    ? AppTheme.primary.withOpacity(0.6)
                                    : (isDark
                                        ? AppTheme.darkCardBorder
                                        : AppTheme.cardBorder)),
                            width: isToday && !isSelected ? 1.5 : 1,
                          ),
                          boxShadow: isSelected
                              ? [
                                  BoxShadow(
                                    color: AppTheme.primary.withOpacity(0.35),
                                    blurRadius: 8,
                                    offset: const Offset(0, 3),
                                  ),
                                ]
                              : null,
                        ),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Text(
                              meta['label'] as String,
                              style: TextStyle(
                                fontSize: 9,
                                fontWeight: FontWeight.w800,
                                color: isSelected ? Colors.white : AppTheme.darkSubtext,
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                            const SizedBox(height: 4),
                            Text(
                              meta['num'] as String,
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w900,
                                color: isSelected
                                    ? Colors.white
                                    : (isDark ? Colors.white : AppTheme.dark),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  );
                }),
              ),
            ),

            // Plans List
            Expanded(
              child: _isLoading && _plans.isEmpty
                  ? const Center(child: CircularProgressIndicator())
                  : _plans.isEmpty
                      ? Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.event_note_rounded,
                                  size: 54, color: AppTheme.grey.withOpacity(0.4)),
                              const SizedBox(height: 12),
                              const Text('No lesson plans for this day',
                                  style: TextStyle(
                                      fontSize: 15,
                                      fontWeight: FontWeight.w700,
                                      color: AppTheme.grey)),
                              const SizedBox(height: 6),
                              ElevatedButton.icon(
                                icon: const Icon(Icons.add, size: 16),
                                label: const Text('Create New Lesson Plan'),
                                onPressed: () => _showAddPlanSheet(isDark),
                              ),
                            ],
                          ),
                        )
                      : ListView.builder(
                          physics: const BouncingScrollPhysics(),
                          padding: const EdgeInsets.fromLTRB(18, 0, 18, 90),
                          itemCount: _plans.length,
                          itemBuilder: (_, i) =>
                              _buildLessonCard(_plans[i], isDark, i),
                        ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildLessonCard(Map<String, dynamic> plan, bool isDark, int index) {
    final isDraft = plan['status'] == 'draft';
    final isCompleted = plan['status'] == 'completed';
    final activities = plan['activities'] is List ? (plan['activities'] as List) : [];

    return InkWell(
      onTap: () => _showPlanDossier(plan),
      borderRadius: BorderRadius.circular(20),
      child: Container(
        margin: const EdgeInsets.only(bottom: 14),
        decoration: BoxDecoration(
          color: isDark ? AppTheme.darkCard : Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isCompleted
                ? Colors.green.shade200
                : (isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
            width: isCompleted ? 1.5 : 1,
          ),
          boxShadow: [
            BoxShadow(
              color: const Color(0xFF0F172A).withOpacity(isDark ? 0.2 : 0.03),
              blurRadius: 10,
              offset: const Offset(0, 3),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header Row
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: isCompleted
                    ? Colors.green.withOpacity(0.08)
                    : AppTheme.primary.withOpacity(isDark ? 0.15 : 0.05),
                borderRadius: const BorderRadius.vertical(top: Radius.circular(19)),
              ),
              child: Row(
                children: [
                  Container(
                    width: 38,
                    height: 38,
                    decoration: BoxDecoration(
                      color: (isCompleted ? AppTheme.green : AppTheme.primary)
                          .withOpacity(0.15),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Icon(
                      isCompleted ? Icons.check_circle_rounded : Icons.menu_book_rounded,
                      color: isCompleted ? AppTheme.green : AppTheme.primary,
                      size: 20,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          plan['title']?.toString() ??
                              plan['class']?.toString() ??
                              'Lesson Topic',
                          style: const TextStyle(
                              fontSize: 14.5, fontWeight: FontWeight.w900),
                        ),
                        Text(
                          '${plan['section']} • ${plan['time']}',
                          style: const TextStyle(
                              fontSize: 11,
                              color: AppTheme.darkSubtext,
                              fontWeight: FontWeight.w600),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: (isCompleted
                              ? AppTheme.green
                              : (isDraft ? AppTheme.orange : AppTheme.blue))
                          .withOpacity(0.12),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      isCompleted ? 'COMPLETED' : (isDraft ? 'DRAFT' : 'READY'),
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w900,
                        color: isCompleted
                            ? AppTheme.green
                            : (isDraft ? AppTheme.orange : AppTheme.blue),
                      ),
                    ),
                  ),
                ],
              ),
            ),

            // Objective & Details
            Padding(
              padding: const EdgeInsets.all(14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Learning Objective (सिकाइ उपलब्धि):',
                    style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                        color: AppTheme.darkSubtext),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    plan['objective']?.toString() ?? 'Master key competencies.',
                    style: const TextStyle(
                        fontSize: 12.5, fontWeight: FontWeight.w600, height: 1.4),
                  ),
                  const SizedBox(height: 10),

                  // Activities list preview
                  ...activities.take(2).map((a) => Padding(
                        padding: const EdgeInsets.only(bottom: 4),
                        child: Row(
                          children: [
                            const Icon(Icons.check_circle_outline_rounded,
                                size: 14, color: AppTheme.primary),
                            const SizedBox(width: 6),
                            Expanded(
                              child: Text(
                                '${a['name']} (${a['duration']})',
                                style: const TextStyle(
                                    fontSize: 11.5, color: AppTheme.darkSubtext),
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                      )),
                  const SizedBox(height: 8),

                  // Homework tag
                  if (plan['homework'] != null &&
                      plan['homework'].toString().isNotEmpty)
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.assignment_outlined,
                              size: 14, color: AppTheme.orange),
                          const SizedBox(width: 6),
                          Expanded(
                            child: Text(
                              'Homework: ${plan['homework']}',
                              style: const TextStyle(
                                  fontSize: 11.5, fontWeight: FontWeight.w700),
                            ),
                          ),
                        ],
                      ),
                    ),
                  const SizedBox(height: 10),

                  // Action hint
                  Row(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      Text(
                        'Tap to View Full Lesson Plan & Activities 👉',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                          color: Color(plan['colorHex'] as int? ?? 0xFF0F4C81),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    ).animate().fadeIn(delay: (index * 40).ms);
  }
}
