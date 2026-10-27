import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../widgets/common_widgets.dart';
import '../providers/auth_provider.dart';
import '../providers/teacher_provider.dart';
import '../providers/theme_provider.dart';
import 'notifications_screen.dart';
import 'attendance_screen.dart';
import 'gradebook_screen.dart';
import 'announcements_screen.dart';
import 'schedule_screen.dart';
import 'students_screen.dart';
import 'assignments_screen.dart';
import 'exams_screen.dart';
import 'classes_screen.dart';
import 'marks_entry_screen.dart';
import 'question_paper_generator_screen.dart';
import 'lesson_planner_screen.dart';
import 'leave_application_screen.dart';
import 'class_incharge_hub_screen.dart';
import 'nepali_calendar_screen.dart';
import 'payslip_screen.dart';
import 'seating_chart_screen.dart';
import 'parent_communication_screen.dart';
import 'behavior_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});
  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final PageController _tipController = PageController();
  int _tipIndex = 0;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<TeacherProvider>(context, listen: false).refreshAll();
    });
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Consumer3<AuthProvider, TeacherProvider, ThemeProvider>(
      builder: (context, auth, teacher, themeProv, child) {
        return Scaffold(
          backgroundColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
          body: SafeArea(
            child: RefreshIndicator(
              onRefresh: () => teacher.refreshAll(),
              color: AppTheme.primary,
              child: CustomScrollView(
                physics: const BouncingScrollPhysics(parent: AlwaysScrollableScrollPhysics()),
                slivers: [
                  // Veda Top App Bar & School Header
                  SliverToBoxAdapter(
                    child: _buildVedaHeader(isDark, auth, themeProv).animate().fadeIn(duration: 350.ms),
                  ),

                  // Quick Stats Strip
                  SliverToBoxAdapter(
                    child: _buildVedaStats(isDark, teacher).animate().fadeIn(delay: 80.ms),
                  ),

                  // Hero: Next Period / Ongoing Class Card
                  SliverToBoxAdapter(
                    child: _buildHeroClassCard(isDark, teacher).animate().fadeIn(delay: 150.ms),
                  ),

                  // Notice & Urgent Announcements Banner
                  SliverToBoxAdapter(
                    child: _buildNoticeBanner(isDark, teacher).animate().fadeIn(delay: 220.ms),
                  ),

                  // Academic Modules (Veda Academic Hub)
                  SliverToBoxAdapter(
                    child: _buildAcademicModules(isDark).animate().fadeIn(delay: 280.ms),
                  ),

                  // Teacher Utilities & HR Tools (Veda Guru Utilities)
                  SliverToBoxAdapter(
                    child: _buildTeacherTools(isDark).animate().fadeIn(delay: 340.ms),
                  ),

                  // Today's Routine Timeline
                  SliverToBoxAdapter(
                    child: _buildRoutineTimeline(isDark, teacher).animate().fadeIn(delay: 400.ms),
                  ),

                  // Pending Assignments & Review Card
                  SliverToBoxAdapter(
                    child: _buildPendingReviews(isDark, teacher).animate().fadeIn(delay: 450.ms),
                  ),

                  // Upcoming Terminal Examinations
                  SliverToBoxAdapter(
                    child: _buildUpcomingExamCard(isDark, teacher).animate().fadeIn(delay: 500.ms),
                  ),

                  // Teaching Tip Carousel
                  SliverToBoxAdapter(
                    child: _buildTeachingTipsCarousel(isDark).animate().fadeIn(delay: 550.ms),
                  ),

                  const SliverToBoxAdapter(child: SizedBox(height: 100)),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  Widget _buildVedaHeader(bool isDark, AuthProvider auth, ThemeProvider themeProv) {
    final teacherProvider = Provider.of<TeacherProvider>(context);
    final unread = teacherProvider.unreadMessagesCount;
    final teacherName = auth.teacherName.isNotEmpty ? auth.teacherName : 'Teacher';
    final schoolName = auth.schoolName.isNotEmpty ? auth.schoolName : 'Nepal School Hub';
    final designation = auth.teacherDesignation.isNotEmpty ? auth.teacherDesignation : 'Faculty Member';

    return Padding(
      padding: const EdgeInsets.fromLTRB(18, 14, 18, 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // School branding tag & quick controls
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF1E293B) : Colors.white,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(
                    color: isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1),
                    width: 0.8,
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.04),
                      blurRadius: 6,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    _buildSchoolLogoBadge(auth.schoolLogo, schoolName),
                    const SizedBox(width: 6),
                    ConstrainedBox(
                      constraints: const BoxConstraints(maxWidth: 200),
                      child: Text(
                        schoolName.toUpperCase(),
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          fontSize: 10.5,
                          fontWeight: FontWeight.w900,
                          letterSpacing: 0.4,
                          color: isDark ? Colors.white : const Color(0xFF0F4C81),
                        ),
                      ),
                    ),
                    const SizedBox(width: 4),
                    const Icon(Icons.verified_rounded, color: AppTheme.primary, size: 13),
                  ],
                ),
              ),
              const Spacer(),

              // Theme Switcher
              CircleIconButton(
                icon: isDark ? Icons.light_mode_rounded : Icons.dark_mode_outlined,
                size: 38,
                iconColor: isDark ? Colors.amber : const Color(0xFF475569),
                onTap: () => themeProv.toggleTheme(),
              ),
              const SizedBox(width: 8),

              // Notification Bell
              CircleIconButton(
                icon: Icons.notifications_outlined,
                size: 38,
                badge: unread,
                onTap: () => Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const NotificationsScreen()),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),

          // Teacher Profile Bar
          Row(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              // Avatar with online status
              Stack(
                children: [
                  Container(
                    width: 50,
                    height: 50,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      gradient: const LinearGradient(
                        colors: [Color(0xFF0F4C81), Color(0xFF2563EB)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      border: Border.all(
                        color: Colors.white,
                        width: 2,
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: AppTheme.primary.withOpacity(0.25),
                          blurRadius: 10,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: Center(
                      child: Text(
                        auth.teacherName.split(' ').map((n) => n.isNotEmpty ? n[0] : '').take(2).join().toUpperCase(),
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w900,
                          fontSize: 16,
                          letterSpacing: 0.5,
                        ),
                      ),
                    ),
                  ),
                  Positioned(
                    right: 0,
                    bottom: 0,
                    child: Container(
                      width: 14,
                      height: 14,
                      decoration: BoxDecoration(
                        color: AppTheme.green,
                        shape: BoxShape.circle,
                        border: Border.all(color: isDark ? AppTheme.darkBg : Colors.white, width: 2),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(width: 14),

              // Teacher Greeting & Designation
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Namaste, $teacherName 🙏',
                      style: const TextStyle(
                        fontSize: 19,
                        fontWeight: FontWeight.w900,
                        letterSpacing: -0.3,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      designation,
                      style: const TextStyle(
                        fontSize: 11.5,
                        color: AppTheme.darkSubtext,
                        fontWeight: FontWeight.w600,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),

          // Nepali Date Bar (Bikram Sambat)
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF1E293B) : const Color(0xFFF1F5F9),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(
                color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
                width: 0.8,
              ),
            ),
            child: const Row(
              children: [
                Icon(Icons.calendar_today_rounded, size: 13, color: AppTheme.primary),
                SizedBox(width: 7),
                Text(
                  '१० असोज २०८१ • बिहीबार (26 Sep 2026)',
                  style: TextStyle(
                    fontSize: 11.5,
                    fontWeight: FontWeight.w700,
                    color: AppTheme.primary,
                  ),
                ),
                Spacer(),
                Text(
                  '2081 B.S.',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    color: AppTheme.darkSubtext,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSchoolLogoBadge(String? logoUrl, String schoolName) {
    if (logoUrl != null && logoUrl.isNotEmpty && logoUrl.startsWith('http')) {
      return Container(
        width: 20,
        height: 20,
        decoration: BoxDecoration(
          shape: BoxShape.circle,
          color: Colors.white,
          border: Border.all(color: AppTheme.primary.withOpacity(0.2), width: 1),
        ),
        clipBehavior: Clip.antiAlias,
        child: Image.network(
          logoUrl,
          fit: BoxFit.cover,
          errorBuilder: (_, __, ___) => const Icon(Icons.school_rounded, size: 13, color: AppTheme.primary),
        ),
      );
    }
    return Container(
      width: 20,
      height: 20,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        color: AppTheme.primary.withOpacity(0.12),
      ),
      child: const Center(
        child: Icon(Icons.school_rounded, size: 12, color: AppTheme.primary),
      ),
    );
  }

  // --- 2. Veda Quick Stats Strip ---
  Widget _buildVedaStats(bool isDark, TeacherProvider teacher) {
    final stats = teacher.stats;
    return Padding(
      padding: const EdgeInsets.fromLTRB(18, 10, 18, 4),
      child: Row(
        children: [
          _buildVedaStatCard(
            title: 'Classes',
            value: '${stats['classes_count'] ?? 4}',
            icon: Icons.menu_book_rounded,
            color: const Color(0xFF2563EB),
            bgLight: const Color(0xFFEFF6FF),
            isDark: isDark,
            onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const ClassesScreen())),
          ),
          const SizedBox(width: 8),
          _buildVedaStatCard(
            title: 'Students',
            value: '${stats['students_count'] ?? 115}',
            icon: Icons.groups_rounded,
            color: const Color(0xFF059669),
            bgLight: const Color(0xFFECFDF5),
            isDark: isDark,
            onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const StudentsScreen())),
          ),
          const SizedBox(width: 8),
          _buildVedaStatCard(
            title: 'Pending',
            value: '${stats['pending_assignments'] ?? 12}',
            icon: Icons.assignment_late_rounded,
            color: const Color(0xFFD97706),
            bgLight: const Color(0xFFFFFBEB),
            isDark: isDark,
            onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const AssignmentsScreen())),
          ),
          const SizedBox(width: 8),
          _buildVedaStatCard(
            title: 'Messages',
            value: '${stats['unread_messages'] ?? 8}',
            icon: Icons.chat_bubble_rounded,
            color: const Color(0xFF7C3AED),
            bgLight: const Color(0xFFF5F3FF),
            isDark: isDark,
            onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const ParentCommunicationScreen())),
          ),
        ],
      ),
    );
  }

  Widget _buildVedaStatCard({
    required String title,
    required String value,
    required IconData icon,
    required Color color,
    required Color bgLight,
    required bool isDark,
    required VoidCallback onTap,
  }) {
    return Expanded(
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(16),
          child: Ink(
            padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
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
                  child: Icon(icon, color: color, size: 18),
                ),
                const SizedBox(height: 6),
                Text(
                  value,
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w900,
                    color: isDark ? Colors.white : AppTheme.dark,
                    letterSpacing: -0.2,
                  ),
                ),
                const SizedBox(height: 1),
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 10.5,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.darkSubtext,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  // --- 3. Hero: Next Period / Live Class Card ---
  Widget _buildHeroClassCard(bool isDark, TeacherProvider teacher) {
    final nextClass = teacher.nextClass;
    final firstClass = teacher.classes.isNotEmpty ? teacher.classes[0] : null;

    final subject = nextClass?['subject']?.toString() ??
        (firstClass is Map ? firstClass['name'] ?? firstClass['subject'] : (firstClass != null ? firstClass.name : 'Mathematics'));
    final classSection = nextClass?['class_name']?.toString() ??
        (firstClass is Map ? firstClass['section'] ?? firstClass['class_name'] : (firstClass != null ? firstClass.section : 'Grade 1 — Section A'));
    final timeStr = nextClass?['time']?.toString() ??
        (firstClass is Map ? firstClass['time'] : (firstClass != null ? firstClass.time : '09:00 AM - 09:45 AM'));
    final roomStr = nextClass?['room']?.toString() ??
        (firstClass is Map ? firstClass['room'] : (firstClass != null ? firstClass.room : 'Room 101'));
    final studentsCount = firstClass is Map ? (firstClass['students'] ?? 25) : (firstClass != null ? firstClass.students : 25);

    return Padding(
      padding: const EdgeInsets.fromLTRB(18, 14, 18, 0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SectionHeader(
            title: 'Upcoming Period',
            subtitle: 'Real-time class schedule & attendance',
            actionLabel: 'All Routine',
            onAction: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const ClassesScreen()),
            ),
          ),
          const SizedBox(height: 10),
          Container(
            padding: const EdgeInsets.all(18),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF0F4C81), Color(0xFF1E3A8A), Color(0xFF2563EB)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(22),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF1E3A8A).withOpacity(0.4),
                  blurRadius: 18,
                  offset: const Offset(0, 8),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Top Status Strip
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.18),
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: Colors.white.withOpacity(0.2), width: 0.8),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Container(
                            width: 7,
                            height: 7,
                            decoration: const BoxDecoration(
                              color: Color(0xFF4ADE80), // Bright green dot
                              shape: BoxShape.circle,
                            ),
                          ),
                          const SizedBox(width: 5),
                          const Text(
                            'UPCOMING CLASS • TODAY',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 10,
                              fontWeight: FontWeight.w800,
                              letterSpacing: 0.5,
                            ),
                          ),
                        ],
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3.5),
                      decoration: BoxDecoration(
                        color: Colors.black.withOpacity(0.25),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        roomStr.toString(),
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 14),

                // Subject Name & Class
                Text(
                  subject.toString(),
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 21,
                    fontWeight: FontWeight.w900,
                    letterSpacing: -0.4,
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  classSection.toString(),
                  style: TextStyle(
                    color: Colors.white.withOpacity(0.85),
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 14),

                // Metadata details
                Row(
                  children: [
                    _buildHeroChip(Icons.access_time_rounded, timeStr.toString()),
                    const SizedBox(width: 10),
                    _buildHeroChip(Icons.people_alt_outlined, '$studentsCount Students'),
                  ],
                ),
                const SizedBox(height: 16),

                // Action Buttons on Hero Card
                Row(
                  children: [
                    Expanded(
                      flex: 6,
                      child: ElevatedButton.icon(
                        onPressed: () => Navigator.push(
                          context,
                          MaterialPageRoute(builder: (_) => const AttendanceScreen()),
                        ),
                        icon: const Icon(Icons.fact_check_rounded, size: 16, color: Color(0xFF0F4C81)),
                        label: const Text(
                          'Take Haziri',
                          style: TextStyle(
                            color: Color(0xFF0F4C81),
                            fontWeight: FontWeight.w800,
                            fontSize: 13,
                          ),
                        ),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.white,
                          foregroundColor: const Color(0xFF0F4C81),
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          elevation: 0,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      flex: 4,
                      child: OutlinedButton.icon(
                        onPressed: () => Navigator.push(
                          context,
                          MaterialPageRoute(builder: (_) => const ClassesScreen()),
                        ),
                        icon: const Icon(Icons.cast_for_education_rounded, size: 15, color: Colors.white),
                        label: const Text(
                          'Smart Class',
                          style: TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w700,
                            fontSize: 12,
                          ),
                        ),
                        style: OutlinedButton.styleFrom(
                          side: BorderSide(color: Colors.white.withOpacity(0.5), width: 1.2),
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
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

  Widget _buildHeroChip(IconData icon, String text) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.12),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 12, color: Colors.white.withOpacity(0.9)),
          const SizedBox(width: 5),
          Text(
            text,
            style: TextStyle(
              color: Colors.white.withOpacity(0.95),
              fontSize: 11,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }

  // --- 4. Notice & Urgent Announcements Banner ---
  Widget _buildNoticeBanner(bool isDark, TeacherProvider teacher) {
    final firstAnnounce = teacher.announcements.isNotEmpty ? teacher.announcements.first : null;
    final title = firstAnnounce is Map ? (firstAnnounce['title'] ?? 'Staff Briefing & Class Notes') : (firstAnnounce != null ? firstAnnounce.title : 'Staff Briefing & Class Notes');
    final date = firstAnnounce is Map ? (firstAnnounce['date'] ?? 'Today') : (firstAnnounce != null ? firstAnnounce.date : 'Today');

    return Padding(
      padding: const EdgeInsets.fromLTRB(18, 18, 18, 0),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: () => Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const AnnouncementsScreen()),
          ),
          borderRadius: BorderRadius.circular(16),
          child: Ink(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF1E293B) : const Color(0xFFFEF2F2),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(
                color: isDark ? const Color(0xFF7F1D1D) : const Color(0xFFFECACA),
                width: 1,
              ),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: const Color(0xFFEF4444).withOpacity(0.15),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(Icons.campaign_rounded, color: Color(0xFFEF4444), size: 20),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                            decoration: BoxDecoration(
                              color: const Color(0xFFEF4444),
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: const Text(
                              'SCHOOL NOTICE',
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 9,
                                fontWeight: FontWeight.w900,
                                letterSpacing: 0.5,
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Text(
                            date.toString(),
                            style: const TextStyle(fontSize: 10, color: AppTheme.darkSubtext),
                          ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Text(
                        title.toString(),
                        style: TextStyle(
                          fontSize: 12.5,
                          fontWeight: FontWeight.w800,
                          color: isDark ? Colors.white : const Color(0xFF991B1B),
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
                const Icon(Icons.chevron_right_rounded, color: Color(0xFFEF4444), size: 20),
              ],
            ),
          ),
        ),
      ),
    );
  }

  // --- 5. Academic Modules (Veda Academic Hub) ---
  Widget _buildAcademicModules(bool isDark) {
    final modules = [
      {
        'label': 'Attendance',
        'sub': 'Haziri',
        'icon': Icons.how_to_reg_rounded,
        'color': const Color(0xFF059669),
        'screen': const AttendanceScreen(),
      },
      {
        'label': 'Enter Marks',
        'sub': 'CAS & Exam',
        'icon': Icons.edit_note_rounded,
        'color': const Color(0xFF2563EB),
        'screen': const MarksEntryScreen(),
      },
      {
        'label': 'AI Question',
        'sub': 'Bloom Tax.',
        'icon': Icons.auto_awesome_rounded,
        'color': const Color(0xFF7C3AED),
        'screen': const QuestionPaperGeneratorScreen(),
      },
      {
        'label': 'Homework',
        'sub': 'Grihakarya',
        'icon': Icons.assignment_rounded,
        'color': const Color(0xFFD97706),
        'screen': const AssignmentsScreen(),
      },
      {
        'label': 'Lesson Plan',
        'sub': 'Syllabus',
        'icon': Icons.import_contacts_rounded,
        'color': const Color(0xFF0284C7),
        'screen': const LessonPlannerScreen(),
      },
      {
        'label': 'Gradebook',
        'sub': 'Analytics',
        'icon': Icons.insights_rounded,
        'color': const Color(0xFFDB2777),
        'screen': const GradebookScreen(),
      },
    ];

    return Padding(
      padding: const EdgeInsets.fromLTRB(18, 22, 18, 0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const SectionHeader(
            title: 'Academic Hub',
            subtitle: 'Curriculum, evaluations & daily teaching tools',
          ),
          const SizedBox(height: 12),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: modules.length,
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 3,
              crossAxisSpacing: 10,
              mainAxisSpacing: 10,
              childAspectRatio: 1.12,
            ),
            itemBuilder: (context, idx) {
              final m = modules[idx];
              final color = m['color'] as Color;
              return _buildSquircleModuleCard(
                label: m['label'] as String,
                sub: m['sub'] as String,
                icon: m['icon'] as IconData,
                color: color,
                isDark: isDark,
                onTap: () => Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => m['screen'] as Widget),
                ),
              );
            },
          ),
        ],
      ),
    );
  }

  // --- 6. Teacher Utilities & HR Tools ---
  Widget _buildTeacherTools(bool isDark) {
    final tools = [
      {
        'label': 'Class Incharge',
        'sub': 'Hub 10-A',
        'icon': Icons.school_rounded,
        'color': const Color(0xFF0F766E),
        'screen': const ClassInchargeHubScreen(),
      },
      {
        'label': 'B.S. Calendar',
        'sub': 'Patro 2081',
        'icon': Icons.calendar_month_rounded,
        'color': const Color(0xFFBE123C),
        'screen': const NepaliCalendarScreen(),
      },
      {
        'label': 'Seating Grid',
        'sub': 'Arrangement',
        'icon': Icons.grid_view_rounded,
        'color': const Color(0xFF6D28D9),
        'screen': const SeatingChartScreen(),
      },
      {
        'label': 'My Salary',
        'sub': 'Payslip (रु.)',
        'icon': Icons.receipt_long_rounded,
        'color': const Color(0xFF15803D),
        'screen': const PayslipScreen(),
      },
      {
        'label': 'Leave Request',
        'sub': 'Bida App',
        'icon': Icons.beach_access_rounded,
        'color': const Color(0xFFC2410C),
        'screen': const LeaveApplicationScreen(),
      },
      {
        'label': 'Parent Chat',
        'sub': 'Messages',
        'icon': Icons.forum_rounded,
        'color': const Color(0xFF0369A1),
        'screen': const ParentCommunicationScreen(),
      },
    ];

    return Padding(
      padding: const EdgeInsets.fromLTRB(18, 22, 18, 0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const SectionHeader(
            title: 'Teacher Power Tools 🇳🇵',
            subtitle: 'Nepali calendar, class incharge, leave & salary',
          ),
          const SizedBox(height: 12),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: tools.length,
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 3,
              crossAxisSpacing: 10,
              mainAxisSpacing: 10,
              childAspectRatio: 1.12,
            ),
            itemBuilder: (context, idx) {
              final t = tools[idx];
              final color = t['color'] as Color;
              return _buildSquircleModuleCard(
                label: t['label'] as String,
                sub: t['sub'] as String,
                icon: t['icon'] as IconData,
                color: color,
                isDark: isDark,
                onTap: () => Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => t['screen'] as Widget),
                ),
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildSquircleModuleCard({
    required String label,
    required String sub,
    required IconData icon,
    required Color color,
    required bool isDark,
    required VoidCallback onTap,
  }) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(18),
        child: Ink(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 10),
          decoration: BoxDecoration(
            color: isDark ? AppTheme.darkCard : Colors.white,
            borderRadius: BorderRadius.circular(18),
            border: Border.all(
              color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
              width: 1,
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
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                width: 38,
                height: 38,
                decoration: BoxDecoration(
                  color: color.withOpacity(isDark ? 0.22 : 0.12),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(icon, color: color, size: 20),
              ),
              const SizedBox(height: 6),
              Text(
                label,
                style: TextStyle(
                  fontSize: 11.5,
                  fontWeight: FontWeight.w800,
                  color: isDark ? Colors.white : const Color(0xFF1E293B),
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              Text(
                sub,
                style: const TextStyle(
                  fontSize: 9.5,
                  color: AppTheme.darkSubtext,
                  fontWeight: FontWeight.w600,
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ],
          ),
        ),
      ),
    );
  }

  // --- 7. Today's Routine Timeline ---
  Widget _buildRoutineTimeline(bool isDark, TeacherProvider teacher) {
    final timelineItems = teacher.timeline;
    return Padding(
      padding: const EdgeInsets.fromLTRB(18, 24, 18, 0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SectionHeader(
            title: "Today's Routine (समय तालिका)",
            subtitle: 'Day periods & assigned classroom sessions',
            actionLabel: 'Full Routine',
            onAction: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const ScheduleScreen()),
            ),
          ),
          const SizedBox(height: 12),
          if (timelineItems.isNotEmpty)
            ...timelineItems.map((item) => _buildTimelineCard(item is Map<String, dynamic> ? item : Map<String, dynamic>.from(item as Map), isDark))
          else if (teacher.classes.isNotEmpty)
            ...teacher.classes.take(3).toList().asMap().entries.map((entry) {
              final c = entry.value;
              final idx = entry.key;
              return _buildTimelineCard({
                'period': 'Period ${idx + 1}',
                'time': c is Map ? (c['time'] ?? '09:00 AM') : '09:00 AM',
                'class': c is Map ? (c['name'] ?? c['subject'] ?? 'Subject') : 'Subject',
                'section': c is Map ? (c['section'] ?? 'Section A') : 'Section A',
                'room': c is Map ? (c['room'] ?? 'Room 101') : 'Room 101',
                'status': idx == 0 ? 'In Progress' : 'Upcoming',
              }, isDark);
            })
          else
            ...AppData.todaySchedule.take(2).map((item) => _buildTimelineCard(item, isDark)),
        ],
      ),
    );
  }

  Widget _buildTimelineCard(Map<String, dynamic> item, bool isDark) {
    final period = item['period']?.toString() ?? 'Period';
    final time = item['time']?.toString() ?? '09:00 AM';
    final subject = (item['class'] ?? item['title'] ?? item['subject'])?.toString() ?? 'General';
    final section = (item['section'] ?? item['description'])?.toString() ?? 'Section A';
    final room = item['room']?.toString() ?? 'Room 101';
    final isBreak = period.toLowerCase().contains('break');
    final isOngoing = item['status'] == 'In Progress' || item['status'] == 'LIVE';

    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkCard : Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isOngoing
              ? AppTheme.primary
              : (isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
          width: isOngoing ? 1.5 : 1,
        ),
      ),
      child: Row(
        children: [
          // Period & Time column
          Container(
            width: 72,
            padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 4),
            decoration: BoxDecoration(
              color: isBreak
                  ? const Color(0xFFEA580C).withOpacity(0.12)
                  : (isOngoing ? AppTheme.primary.withOpacity(0.12) : (isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9))),
              borderRadius: BorderRadius.circular(10),
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
                    fontWeight: FontWeight.w600,
                    color: AppTheme.darkSubtext,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 12),

          // Subject & Section info
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        subject,
                        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800),
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
                          'LIVE',
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
                      style: const TextStyle(fontSize: 11, color: AppTheme.darkSubtext),
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

  // --- 8. Pending Assignment Reviews ---
  Widget _buildPendingReviews(bool isDark, TeacherProvider teacher) {
    final list = teacher.assignments.isNotEmpty ? teacher.assignments : AppData.assignments;
    return Padding(
      padding: const EdgeInsets.fromLTRB(18, 22, 18, 0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SectionHeader(
            title: 'Pending Homework Reviews (गृहकार्य)',
            subtitle: 'Submissions awaiting evaluation',
            actionLabel: 'All Grihakarya',
            onAction: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const AssignmentsScreen()),
            ),
          ),
          const SizedBox(height: 12),
          ...list.take(2).map((a) {
            final title = a is Map ? (a['title'] ?? 'Homework') : a.title;
            final submitted = a is Map ? (a['submitted_count'] ?? a['submitted'] ?? 0) : a.submitted;
            final total = a is Map ? (a['total_count'] ?? a['total'] ?? 25) : a.total;
            final classSec = a is Map ? (a['class_name'] ?? a['classSection'] ?? 'Grade 10') : a.classSection;
            final due = a is Map ? (a['due_date'] ?? a['dueDate'] ?? 'Tomorrow') : a.dueDate;
            final totalInt = (total is int && total > 0) ? total : 25;
            final subInt = submitted is int ? submitted : 0;
            final pct = (subInt / totalInt).clamp(0.0, 1.0);

            return Container(
              margin: const EdgeInsets.only(bottom: 10),
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: isDark ? AppTheme.darkCard : Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(
                  color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
                  width: 1,
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          title.toString(),
                          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800),
                        ),
                      ),
                      Text(
                        '$subInt/$totalInt',
                        style: const TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w900,
                          color: AppTheme.primary,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      Text(
                        '$classSec • Due $due',
                        style: const TextStyle(fontSize: 11, color: AppTheme.darkSubtext),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  ClipRRect(
                    borderRadius: BorderRadius.circular(6),
                    child: LinearProgressIndicator(
                      value: pct,
                      minHeight: 7,
                      backgroundColor: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
                      valueColor: const AlwaysStoppedAnimation<Color>(AppTheme.primary),
                    ),
                  ),
                ],
              ),
            );
          }),
        ],
      ),
    );
  }

  // --- 9. Upcoming Exam Card ---
  Widget _buildUpcomingExamCard(bool isDark, TeacherProvider teacher) {
    final firstExam = teacher.exams.isNotEmpty ? teacher.exams.first : null;
    final subject = firstExam is Map ? (firstExam['subject'] ?? firstExam['name'] ?? 'Term Exam') : (firstExam != null ? firstExam.subject : 'Terminal Examination');
    final classSec = firstExam is Map ? (firstExam['class_name'] ?? firstExam['classSection'] ?? 'All Classes') : (firstExam != null ? firstExam.classSection : 'Secondary Wing');
    final room = firstExam is Map ? (firstExam['room'] ?? 'Exam Hall') : (firstExam != null ? firstExam.room : 'Hall A');
    final date = firstExam is Map ? (firstExam['date'] ?? 'Upcoming') : (firstExam != null ? firstExam.date : 'Oct 15, 2026');
    final time = firstExam is Map ? (firstExam['time'] ?? '10:00 AM') : (firstExam != null ? firstExam.time : '10:00 AM - 01:00 PM');
    final daysLeft = firstExam is Map ? (firstExam['days_left'] ?? 14) : (firstExam != null ? firstExam.daysLeft : 14);

    return Padding(
      padding: const EdgeInsets.fromLTRB(18, 20, 18, 0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SectionHeader(
            title: 'Upcoming Examination (परीक्षा)',
            actionLabel: 'Exam Routine',
            onAction: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const ExamsScreen()),
            ),
          ),
          const SizedBox(height: 10),
          AppCard(
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const ExamsScreen()),
            ),
            child: Row(
              children: [
                Container(
                  width: 50,
                  height: 50,
                  decoration: BoxDecoration(
                    color: const Color(0xFF2563EB).withOpacity(0.12),
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: const Icon(Icons.quiz_rounded, color: Color(0xFF2563EB), size: 26),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        subject.toString(),
                        style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        '$classSec • $room',
                        style: const TextStyle(fontSize: 11.5, color: AppTheme.darkSubtext),
                      ),
                      Text(
                        '$date • $time',
                        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppTheme.primary),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  decoration: BoxDecoration(
                    color: const Color(0xFF2563EB).withOpacity(0.12),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Column(
                    children: [
                      Text(
                        '$daysLeft',
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w900,
                          color: Color(0xFF2563EB),
                        ),
                      ),
                      const Text(
                        'days left',
                        style: TextStyle(fontSize: 9, color: AppTheme.darkSubtext),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // --- 10. Teaching Tips Carousel ---
  Widget _buildTeachingTipsCarousel(bool isDark) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(18, 22, 18, 0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const SectionHeader(
            title: '💡 Teacher Pedagogical Insights',
            subtitle: 'Best practices for Nepal curriculum excellence',
          ),
          const SizedBox(height: 10),
          SizedBox(
            height: 82,
            child: PageView.builder(
              controller: _tipController,
              onPageChanged: (i) => setState(() => _tipIndex = i),
              itemCount: AppData.teachingTips.length,
              itemBuilder: (_, i) {
                final tip = AppData.teachingTips[i];
                return AppCard(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                  child: Row(
                    children: [
                      Icon(
                        IconData(tip['icon'] as int, fontFamily: 'MaterialIcons'),
                        color: AppTheme.primary,
                        size: 24,
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          tip['tip'] as String,
                          style: const TextStyle(fontSize: 12, height: 1.35, fontWeight: FontWeight.w600),
                        ),
                      ),
                    ],
                  ),
                );
              },
            ),
          ),
          const SizedBox(height: 8),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: List.generate(
              AppData.teachingTips.length,
              (i) => AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                margin: const EdgeInsets.symmetric(horizontal: 3),
                width: i == _tipIndex ? 16 : 5,
                height: 5,
                decoration: BoxDecoration(
                  color: i == _tipIndex ? AppTheme.primary : AppTheme.grey.withOpacity(0.3),
                  borderRadius: BorderRadius.circular(3),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
