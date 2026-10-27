import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../widgets/common_widgets.dart';
import '../providers/teacher_provider.dart';
import 'students_screen.dart';
import 'attendance_screen.dart';
import 'gradebook_screen.dart';
import 'assignments_screen.dart';
import 'marks_entry_screen.dart';

class ClassesScreen extends StatefulWidget {
  const ClassesScreen({super.key});

  @override
  State<ClassesScreen> createState() => _ClassesScreenState();
}

class _ClassesScreenState extends State<ClassesScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  int _selectedDayIndex = 4; // Thursday (Bihibar) default for today
  String _searchQuery = '';

  final List<String> _nepaliDays = [
    'Sunday (आइत)',
    'Monday (सोम)',
    'Tuesday (मंगल)',
    'Wednesday (बुध)',
    'Thursday (बिही)',
    'Friday (शुक्र)',
  ];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
      body: SafeArea(
        child: NestedScrollView(
          headerSliverBuilder: (context, innerBoxIsScrolled) => [
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(18, 14, 18, 12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Top App Bar
                    Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                decoration: BoxDecoration(
                                  color: AppTheme.primary.withOpacity(0.1),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: const Text(
                                  'ACADEMIC YEAR 2081 B.S.',
                                  style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.w800,
                                    color: AppTheme.primary,
                                    letterSpacing: 0.5,
                                  ),
                                ),
                              ),
                              const SizedBox(height: 4),
                              const Text(
                                'My Classes & Routine',
                                style: TextStyle(
                                  fontSize: 22,
                                  fontWeight: FontWeight.w900,
                                  letterSpacing: -0.4,
                                ),
                              ),
                            ],
                          ),
                        ),
                        CircleIconButton(
                          icon: Icons.search_rounded,
                          size: 40,
                          onTap: () {},
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),

                    // Modern Segmented Tab Selector
                    Container(
                      padding: const EdgeInsets.all(4),
                      decoration: BoxDecoration(
                        color: isDark ? AppTheme.darkCard : const Color(0xFFE2E8F0),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(
                          color: isDark ? AppTheme.darkCardBorder : const Color(0xFFCBD5E1),
                          width: 0.8,
                        ),
                      ),
                      child: TabBar(
                        controller: _tabController,
                        indicatorSize: TabBarIndicatorSize.tab,
                        indicator: BoxDecoration(
                          color: isDark ? const Color(0xFF2563EB) : Colors.white,
                          borderRadius: BorderRadius.circular(12),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withOpacity(isDark ? 0.3 : 0.08),
                              blurRadius: 8,
                              offset: const Offset(0, 2),
                            ),
                          ],
                        ),
                        labelColor: isDark ? Colors.white : AppTheme.primary,
                        unselectedLabelColor: AppTheme.darkSubtext,
                        labelStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                        unselectedLabelStyle: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                        tabs: const [
                          Tab(text: '📚 Assigned Classes (4)'),
                          Tab(text: '🗓️ Weekly Routine (तालिका)'),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
          body: TabBarView(
            controller: _tabController,
            children: [
              _buildClassesListTab(isDark),
              _buildWeeklyRoutineTab(isDark),
            ],
          ),
        ),
      ),
    );
  }

  // --- TAB 1: Assigned Classes List ---
  Widget _buildClassesListTab(bool isDark) {
    final teacher = Provider.of<TeacherProvider>(context);

    List<TeacherClass> classList = [];
    if (teacher.classes.isNotEmpty) {
      classList = teacher.classes.asMap().entries.map((entry) {
        final idx = entry.key;
        final c = entry.value;
        if (c is TeacherClass) return c;
        final name = c is Map ? (c['name'] ?? c['subject'] ?? 'Subject') : 'Subject';
        final section = c is Map ? (c['section'] ?? c['class_name'] ?? 'Grade 10') : 'Grade 10';
        final room = c is Map ? (c['room'] ?? 'Room 101') : 'Room 101';
        final time = c is Map ? (c['time'] ?? '09:00 AM') : '09:00 AM';
        final students = c is Map ? (c['students'] ?? 25) : 25;
        final colorHex = c is Map ? (c['colorHex'] ?? 0xFFE0F2FE) : 0xFFE0F2FE;
        final isOngoing = idx == 0;
        return TeacherClass(
          name: name.toString(),
          section: section.toString(),
          room: room.toString(),
          time: time.toString(),
          subject: name.toString(),
          students: students is int ? students : 25,
          colorHex: colorHex is int ? colorHex : 0xFFE0F2FE,
          iconCode: 0xe3e9,
          avgGrade: 3.6,
          isOngoing: isOngoing,
        );
      }).toList();
    } else {
      classList = AppData.classes;
    }

    final filtered = classList.where((c) {
      if (_searchQuery.isEmpty) return true;
      return c.name.toLowerCase().contains(_searchQuery.toLowerCase()) ||
          c.section.toLowerCase().contains(_searchQuery.toLowerCase());
    }).toList();

    final totalStudents = classList.fold<int>(0, (sum, c) => sum + c.students);

    return SingleChildScrollView(
      physics: const BouncingScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(18, 6, 18, 100),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Top Quick Stats Summary
          Row(
            children: [
              _buildSummaryCard(
                label: 'Active Classes',
                value: '${classList.length}',
                icon: Icons.menu_book_rounded,
                color: const Color(0xFF2563EB),
                bgLight: const Color(0xFFEFF6FF),
                isDark: isDark,
              ),
              const SizedBox(width: 10),
              _buildSummaryCard(
                label: 'Total Students',
                value: '$totalStudents',
                icon: Icons.groups_rounded,
                color: const Color(0xFF059669),
                bgLight: const Color(0xFFECFDF5),
                isDark: isDark,
              ),
              const SizedBox(width: 10),
              _buildSummaryCard(
                label: 'Avg Class GPA',
                value: '3.6',
                icon: Icons.stars_rounded,
                color: const Color(0xFFD97706),
                bgLight: const Color(0xFFFFFBEB),
                isDark: isDark,
              ),
            ],
          ),
          const SizedBox(height: 20),

          // Section Header
          SectionHeader(
            title: 'Assigned Subject Sections',
            subtitle: 'Curriculum progress, student roster & marks',
            trailing: Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: AppTheme.primary.withOpacity(0.08),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                '${filtered.length} Subjects',
                style: const TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w800,
                  color: AppTheme.primary,
                ),
              ),
            ),
          ),
          const SizedBox(height: 12),

          // Class Cards List
          ...filtered.asMap().entries.map((e) {
            final idx = e.key;
            final cls = e.value;
            return _buildVedaClassCard(cls, idx, isDark)
                .animate()
                .fadeIn(delay: (idx * 80).ms)
                .slideY(begin: 0.08);
          }),
        ],
      ),
    );
  }

  Widget _buildSummaryCard({
    required String label,
    required String value,
    required IconData icon,
    required Color color,
    required Color bgLight,
    required bool isDark,
  }) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 10),
        decoration: BoxDecoration(
          color: isDark ? AppTheme.darkCard : Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
            width: 1,
          ),
          boxShadow: [
            BoxShadow(
              color: const Color(0xFF0F172A).withOpacity(isDark ? 0.2 : 0.03),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          children: [
            Container(
              padding: const EdgeInsets.all(7),
              decoration: BoxDecoration(
                color: isDark ? color.withOpacity(0.18) : bgLight,
                shape: BoxShape.circle,
              ),
              child: Icon(icon, color: color, size: 20),
            ),
            const SizedBox(height: 6),
            Text(
              value,
              style: TextStyle(
                fontSize: 17,
                fontWeight: FontWeight.w900,
                color: color,
                letterSpacing: -0.2,
              ),
            ),
            const SizedBox(height: 1),
            Text(
              label,
              style: const TextStyle(
                fontSize: 10,
                fontWeight: FontWeight.w600,
                color: AppTheme.darkSubtext,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildVedaClassCard(TeacherClass cls, int index, bool isDark) {
    IconData getSubjectIcon(String name) {
      if (name.contains('Compulsory')) return Icons.calculate_rounded;
      if (name.contains('Opt.')) return Icons.square_foot_rounded;
      if (name.contains('Calculus')) return Icons.auto_graph_rounded;
      return Icons.bar_chart_rounded;
    }

    Color getSubjectColor(int index) {
      switch (index % 4) {
        case 0:
          return const Color(0xFF2563EB); // Blue
        case 1:
          return const Color(0xFF7C3AED); // Purple
        case 2:
          return const Color(0xFF059669); // Emerald
        default:
          return const Color(0xFFD97706); // Amber
      }
    }

    final color = getSubjectColor(index);
    final isOngoing = index == 0;

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkCard : Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: isOngoing
              ? AppTheme.primary
              : (isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
          width: isOngoing ? 1.5 : 1,
        ),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withOpacity(isDark ? 0.25 : 0.04),
            blurRadius: 14,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: () => Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => ClassDetailScreen(cls: cls)),
          ),
          borderRadius: BorderRadius.circular(20),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Top Row: Icon + Subject + Student Count Pill
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      width: 48,
                      height: 48,
                      decoration: BoxDecoration(
                        color: color.withOpacity(isDark ? 0.2 : 0.12),
                        borderRadius: BorderRadius.circular(14),
                      ),
                      child: Icon(getSubjectIcon(cls.name), size: 24, color: color),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Expanded(
                                child: Text(
                                  cls.name,
                                  style: const TextStyle(
                                    fontSize: 15,
                                    fontWeight: FontWeight.w900,
                                    letterSpacing: -0.2,
                                  ),
                                ),
                              ),
                              if (isOngoing)
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
                                  decoration: BoxDecoration(
                                    color: AppTheme.green.withOpacity(0.15),
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: const Text(
                                    'LIVE NOW',
                                    style: TextStyle(
                                      fontSize: 9,
                                      fontWeight: FontWeight.w900,
                                      color: AppTheme.green,
                                    ),
                                  ),
                                ),
                            ],
                          ),
                          const SizedBox(height: 2),
                          Text(
                            cls.section,
                            style: const TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w700,
                              color: AppTheme.primary,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                      decoration: BoxDecoration(
                        color: isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Column(
                        children: [
                          Text(
                            '${cls.students}',
                            style: TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.w900,
                              color: isDark ? Colors.white : AppTheme.dark,
                            ),
                          ),
                          const Text(
                            'students',
                            style: TextStyle(fontSize: 9, color: AppTheme.darkSubtext),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),

                // Timing & Room Pills
                Wrap(
                  spacing: 8,
                  runSpacing: 6,
                  children: [
                    _buildPillChip(Icons.access_time_rounded, cls.time, isDark),
                    _buildPillChip(Icons.location_on_outlined, cls.room, isDark),
                  ],
                ),
                const SizedBox(height: 12),

                // Syllabus & GPA Progress Bar
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Syllabus Covered & Avg GPA',
                      style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppTheme.darkSubtext),
                    ),
                    Text(
                      '${cls.avgGrade.toStringAsFixed(1)} / 4.0 GPA',
                      style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800, color: AppTheme.primary),
                    ),
                  ],
                ),
                const SizedBox(height: 6),
                ClipRRect(
                  borderRadius: BorderRadius.circular(4),
                  child: LinearProgressIndicator(
                    value: cls.avgGrade / 4.0,
                    minHeight: 6,
                    backgroundColor: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
                    valueColor: AlwaysStoppedAnimation<Color>(color),
                  ),
                ),
                const SizedBox(height: 14),

                // Divider line
                Divider(
                  height: 1,
                  color: isDark ? AppTheme.darkCardBorder : const Color(0xFFF1F5F9),
                ),
                const SizedBox(height: 10),

                // Quick Action Buttons on Card
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    _buildClassQuickBtn(
                      icon: Icons.fact_check_outlined,
                      label: 'Haziri',
                      color: AppTheme.green,
                      onTap: () => Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const AttendanceScreen()),
                      ),
                    ),
                    _buildClassQuickBtn(
                      icon: Icons.groups_outlined,
                      label: 'Students',
                      color: AppTheme.blue,
                      onTap: () => Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => StudentsScreen(filterClass: cls.section)),
                      ),
                    ),
                    _buildClassQuickBtn(
                      icon: Icons.assignment_outlined,
                      label: 'Grihakarya',
                      color: AppTheme.orange,
                      onTap: () => Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const AssignmentsScreen()),
                      ),
                    ),
                    _buildClassQuickBtn(
                      icon: Icons.edit_note_rounded,
                      label: 'Marks',
                      color: AppTheme.purple,
                      onTap: () => Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const MarksEntryScreen()),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildPillChip(IconData icon, String text, bool isDark) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4.5),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E293B) : const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(
          color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
          width: 0.8,
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 12, color: AppTheme.darkSubtext),
          const SizedBox(width: 4),
          Text(
            text,
            style: const TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w600,
              color: AppTheme.darkSubtext,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildClassQuickBtn({
    required IconData icon,
    required String label,
    required Color color,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 4),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 14, color: color),
            const SizedBox(width: 4),
            Text(
              label,
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w700,
                color: color,
              ),
            ),
          ],
        ),
      ),
    );
  }

  // --- TAB 2: Weekly Routine Timetable ---
  Widget _buildWeeklyRoutineTab(bool isDark) {
    return SingleChildScrollView(
      physics: const BouncingScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(18, 6, 18, 100),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Day Selector Pills (Sun - Fri)
          SizedBox(
            height: 42,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              itemCount: _nepaliDays.length,
              itemBuilder: (context, idx) {
                final isSelected = idx == _selectedDayIndex;
                return GestureDetector(
                  onTap: () => setState(() => _selectedDayIndex = idx),
                  child: Container(
                    margin: const EdgeInsets.only(right: 8),
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                    decoration: BoxDecoration(
                      color: isSelected
                          ? AppTheme.primary
                          : (isDark ? AppTheme.darkCard : Colors.white),
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(
                        color: isSelected
                            ? AppTheme.primary
                            : (isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
                        width: 1,
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
                    child: Center(
                      child: Text(
                        _nepaliDays[idx],
                        style: TextStyle(
                          color: isSelected
                              ? Colors.white
                              : (isDark ? Colors.white70 : const Color(0xFF475569)),
                          fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
                          fontSize: 12,
                        ),
                      ),
                    ),
                  ),
                );
              },
            ),
          ),
          const SizedBox(height: 18),

          SectionHeader(
            title: '${_nepaliDays[_selectedDayIndex]} Schedule',
            subtitle: 'Total 4 Class Periods • 1 Mentorship Hour',
          ),
          const SizedBox(height: 12),

          // Daily Routine Period Cards
          ...AppData.todaySchedule.map((item) => _buildRoutinePeriodCard(item, isDark)),
        ],
      ),
    );
  }

  Widget _buildRoutinePeriodCard(Map<String, dynamic> item, bool isDark) {
    final period = item['period'] as String;
    final time = item['time'] as String;
    final subject = item['class'] as String;
    final section = item['section'] as String;
    final room = item['room'] as String;
    final isBreak = period.toLowerCase().contains('break');
    final isOngoing = item['status'] == 'In Progress';

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkCard : Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(
          color: isOngoing
              ? AppTheme.primary
              : (isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
          width: isOngoing ? 1.5 : 1,
        ),
      ),
      child: Row(
        children: [
          Container(
            width: 74,
            padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 6),
            decoration: BoxDecoration(
              color: isBreak
                  ? const Color(0xFFEA580C).withOpacity(0.12)
                  : (isOngoing ? AppTheme.primary.withOpacity(0.12) : (isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9))),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Column(
              children: [
                Text(
                  period,
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w900,
                    color: isBreak
                        ? const Color(0xFFEA580C)
                        : (isOngoing ? AppTheme.primary : (isDark ? Colors.white70 : const Color(0xFF475569))),
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  time.split(' - ')[0],
                  style: const TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w700,
                    color: AppTheme.darkSubtext,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        subject,
                        style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800),
                      ),
                    ),
                    if (isOngoing)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: AppTheme.green.withOpacity(0.15),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: const Text(
                          'ONGOING',
                          style: TextStyle(
                            fontSize: 9,
                            fontWeight: FontWeight.w900,
                            color: AppTheme.green,
                          ),
                        ),
                      ),
                  ],
                ),
                const SizedBox(height: 3),
                Row(
                  children: [
                    Text(
                      section,
                      style: const TextStyle(fontSize: 11.5, color: AppTheme.darkSubtext),
                    ),
                    const Spacer(),
                    Icon(Icons.location_on_outlined, size: 12, color: isDark ? Colors.white60 : Colors.black54),
                    const SizedBox(width: 2),
                    Text(
                      room,
                      style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppTheme.darkSubtext),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// --- Class Detail Screen ---
class ClassDetailScreen extends StatelessWidget {
  final TeacherClass cls;
  const ClassDetailScreen({super.key, required this.cls});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
      appBar: AppBar(
        title: Text('${cls.name} (${cls.section})'),
        leading: CircleIconButton(
          icon: Icons.arrow_back_ios_new_rounded,
          onTap: () => Navigator.pop(context),
          size: 38,
        ),
        leadingWidth: 60,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(18, 8, 18, 30),
        child: Column(
          children: [
            // Header Gradient Hero Card
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF0F4C81), Color(0xFF1E3A8A), Color(0xFF2563EB)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(22),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF1E3A8A).withOpacity(0.35),
                    blurRadius: 18,
                    offset: const Offset(0, 8),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    cls.name,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 22,
                      fontWeight: FontWeight.w900,
                      letterSpacing: -0.3,
                    ),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    cls.section,
                    style: TextStyle(
                      color: Colors.white.withOpacity(0.85),
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      _buildDetailStatChip(Icons.groups_rounded, '${cls.students}', 'Students'),
                      const SizedBox(width: 10),
                      _buildDetailStatChip(Icons.location_on_outlined, cls.room.split(' ')[0], 'Room'),
                      const SizedBox(width: 10),
                      _buildDetailStatChip(Icons.stars_rounded, '${cls.avgGrade.toStringAsFixed(1)}', 'Avg GPA'),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Quick Academic Actions
            const SectionHeader(title: 'Quick Academic Actions'),
            const SizedBox(height: 12),
            GridView.count(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              crossAxisCount: 2,
              crossAxisSpacing: 10,
              mainAxisSpacing: 10,
              childAspectRatio: 2.1,
              children: [
                _buildActionTile(
                  icon: Icons.fact_check_rounded,
                  label: 'Take Haziri',
                  color: AppTheme.green,
                  isDark: isDark,
                  onTap: () => Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const AttendanceScreen()),
                  ),
                ),
                _buildActionTile(
                  icon: Icons.edit_note_rounded,
                  label: 'Enter Marks',
                  color: AppTheme.blue,
                  isDark: isDark,
                  onTap: () => Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const MarksEntryScreen()),
                  ),
                ),
                _buildActionTile(
                  icon: Icons.assignment_rounded,
                  label: 'Grihakarya',
                  color: AppTheme.orange,
                  isDark: isDark,
                  onTap: () => Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const AssignmentsScreen()),
                  ),
                ),
                _buildActionTile(
                  icon: Icons.people_alt_rounded,
                  label: 'Student Roster',
                  color: AppTheme.purple,
                  isDark: isDark,
                  onTap: () => Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => StudentsScreen(filterClass: cls.section)),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 22),

            // Top Students in this class
            SectionHeader(
              title: 'Student Roster (${cls.section})',
              actionLabel: 'View All',
              onAction: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => StudentsScreen(filterClass: cls.section)),
              ),
            ),
            const SizedBox(height: 12),
            ...teacher.studentList.take(5).map((s) => Container(
              margin: const EdgeInsets.only(bottom: 8),
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: isDark ? AppTheme.darkCard : Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(
                  color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
                  width: 1,
                ),
              ),
              child: Row(
                children: [
                  Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: AppTheme.primary.withOpacity(0.1),
                    ),
                    child: Center(
                      child: Text(
                        s.avatar,
                        style: const TextStyle(
                          color: AppTheme.primary,
                          fontWeight: FontWeight.w900,
                          fontSize: 13,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          s.name,
                          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800),
                        ),
                        Text(
                          'Roll No: ${s.rollNo} • GPA: ${s.gpa.toStringAsFixed(2)} • Haziri: ${(s.attendance * 100).toInt()}%',
                          style: const TextStyle(fontSize: 11, color: AppTheme.darkSubtext),
                        ),
                      ],
                    ),
                  ),
                  GradeBadge(grade: s.grade),
                ],
              ),
            )),
          ],
        ),
      ),
    );
  }

  Widget _buildDetailStatChip(IconData icon, String value, String label) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
        decoration: BoxDecoration(
          color: Colors.white.withOpacity(0.14),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Column(
          children: [
            Icon(icon, color: Colors.white, size: 16),
            const SizedBox(height: 3),
            Text(
              value,
              style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.w900),
            ),
            Text(
              label,
              style: TextStyle(color: Colors.white.withOpacity(0.75), fontSize: 9.5),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildActionTile({
    required IconData icon,
    required String label,
    required Color color,
    required bool isDark,
    required VoidCallback onTap,
  }) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(14),
        child: Ink(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
          decoration: BoxDecoration(
            color: isDark ? AppTheme.darkCard : Colors.white,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(
              color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
              width: 1,
            ),
          ),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: color.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(icon, color: color, size: 18),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  label,
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w800,
                    color: isDark ? Colors.white : AppTheme.dark,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
