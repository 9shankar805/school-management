import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../widgets/common_widgets.dart';
import '../providers/auth_provider.dart';
import '../providers/theme_provider.dart';
import '../providers/teacher_provider.dart';
import 'login_screen.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final themeProvider = Provider.of<ThemeProvider>(context);
    final authProvider = Provider.of<AuthProvider>(context);
    final teacherProvider = Provider.of<TeacherProvider>(context);
    final isDark = themeProvider.isDark;

    final name = authProvider.teacherName.isNotEmpty
        ? authProvider.teacherName
        : 'Teacher';
    final email = authProvider.teacherEmail.isNotEmpty
        ? authProvider.teacherEmail
        : 'teacher@nshlms.com';
    final designation = authProvider.teacherDesignation.isNotEmpty
        ? authProvider.teacherDesignation
        : 'Faculty Member';
    final school = authProvider.schoolName.isNotEmpty
        ? authProvider.schoolName
        : 'Nepal School Hub (NSHLMS)';
    final initials = name
        .split(' ')
        .map((e) => e.isNotEmpty ? e[0] : '')
        .take(2)
        .join();

    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
      appBar: AppBar(
        title: const Text('My Profile (मेरो प्रोफाइल)'),
        automaticallyImplyLeading: false,
        actions: [
          // Theme switch pill
          GestureDetector(
            onTap: () => themeProvider.toggle(),
            child: Container(
              width: 52,
              height: 28,
              margin: const EdgeInsets.only(right: 16),
              decoration: BoxDecoration(
                color: isDark ? AppTheme.primary : AppTheme.grey.withOpacity(0.3),
                borderRadius: BorderRadius.circular(14),
              ),
              child: AnimatedAlign(
                duration: const Duration(milliseconds: 200),
                alignment: isDark ? Alignment.centerRight : Alignment.centerLeft,
                child: Container(
                  width: 22,
                  height: 22,
                  margin: const EdgeInsets.all(3),
                  decoration: const BoxDecoration(
                    color: Colors.white,
                    shape: BoxShape.circle,
                  ),
                  child: Icon(
                    isDark ? Icons.dark_mode_rounded : Icons.light_mode_rounded,
                    size: 13,
                    color: isDark ? AppTheme.primary : AppTheme.orange,
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
      body: SingleChildScrollView(
        physics: const BouncingScrollPhysics(),
        padding: const EdgeInsets.fromLTRB(18, 6, 18, 100),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Teacher Digital ID Card (Veda Smart Guru Pass)
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [
                    Color(0xFF0F4C81),
                    Color(0xFF1E3A8A),
                    Color(0xFF2563EB),
                  ],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(24),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF0F4C81).withOpacity(0.4),
                    blurRadius: 18,
                    offset: const Offset(0, 8),
                  ),
                ],
              ),
              child: Column(
                children: [
                  // Top Row: School Tag & Hologram
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: Row(
                          children: [
                            Container(
                              width: 26,
                              height: 26,
                              decoration: BoxDecoration(
                                color: Colors.white,
                                shape: BoxShape.circle,
                                boxShadow: [
                                  BoxShadow(color: Colors.black.withOpacity(0.15), blurRadius: 4),
                                ],
                              ),
                              clipBehavior: Clip.antiAlias,
                              child: authProvider.schoolLogo != null && authProvider.schoolLogo!.startsWith('http')
                                  ? Image.network(
                                      authProvider.schoolLogo!,
                                      fit: BoxFit.cover,
                                      errorBuilder: (_, __, ___) => const Icon(Icons.school_rounded, size: 15, color: Color(0xFF0F4C81)),
                                    )
                                  : const Icon(Icons.school_rounded, size: 15, color: Color(0xFF0F4C81)),
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                school.toUpperCase(),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 11,
                                  fontWeight: FontWeight.w900,
                                  letterSpacing: 0.8,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 8),
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: Colors.white.withOpacity(0.2),
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(
                            color: Colors.white.withOpacity(0.3),
                            width: 0.8,
                          ),
                        ),
                        child: const Text(
                          'NEB VERIFIED',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 9.5,
                            fontWeight: FontWeight.w900,
                            letterSpacing: 0.5,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 18),

                  // Avatar & Name Row
                  Row(
                    children: [
                      Stack(
                        alignment: Alignment.bottomRight,
                        children: [
                          Container(
                            width: 68,
                            height: 68,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              gradient: const LinearGradient(
                                colors: [Color(0xFF38BDF8), Color(0xFF818CF8)],
                              ),
                              border: Border.all(
                                color: Colors.white,
                                width: 2.5,
                              ),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withOpacity(0.2),
                                  blurRadius: 10,
                                  offset: const Offset(0, 4),
                                ),
                              ],
                            ),
                            child: Center(
                              child: Text(
                                initials.isNotEmpty ? initials : 'SJ',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 24,
                                  fontWeight: FontWeight.w900,
                                ),
                              ),
                            ),
                          ),
                          Container(
                            width: 22,
                            height: 22,
                            decoration: BoxDecoration(
                              color: AppTheme.green,
                              shape: BoxShape.circle,
                              border: Border.all(
                                color: const Color(0xFF0F4C81),
                                width: 2,
                              ),
                            ),
                            child: const Icon(
                              Icons.check,
                              size: 13,
                              color: Colors.white,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              name,
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 17,
                                fontWeight: FontWeight.w900,
                                letterSpacing: -0.2,
                              ),
                            ),
                            const SizedBox(height: 3),
                            Text(
                              designation,
                              style: const TextStyle(
                                color: Color(0xFFBAE6FD),
                                fontSize: 12,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                            const SizedBox(height: 3),
                            Text(
                              email,
                              style: TextStyle(
                                color: Colors.white.withOpacity(0.8),
                                fontSize: 11,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 18),

                  // Divider
                  Divider(
                    color: Colors.white.withOpacity(0.2),
                    height: 1,
                  ),
                  const SizedBox(height: 14),

                  // ID and Verification Footer
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Row(
                        children: [
                          const Icon(
                            Icons.badge_outlined,
                            size: 14,
                            color: Colors.white70,
                          ),
                          const SizedBox(width: 5),
                          Text(
                            'ID: TCH-${authProvider.teacherProfile?['id'] ?? '2081-01'}',
                            style: TextStyle(
                              color: Colors.white.withOpacity(0.9),
                              fontSize: 11.5,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                        ],
                      ),
                      Row(
                        children: [
                          const Icon(
                            Icons.fingerprint_rounded,
                            size: 14,
                            color: Color(0xFFFBBF24),
                          ),
                          const SizedBox(width: 5),
                          const Text(
                            'Biometric Active',
                            style: TextStyle(
                              color: Color(0xFFFBBF24),
                              fontSize: 11.5,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ],
              ),
            ).animate().fadeIn().slideY(begin: 0.1, end: 0),
            const SizedBox(height: 16),

            // 5 Key Stats Row
            Container(
              padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 10),
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
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceAround,
                children: [
                  _StatBox(label: 'Classes', value: '${teacherProvider.classesCount}', icon: Icons.class_rounded, color: AppTheme.primary),
                  _StatBox(label: 'Students', value: '${teacherProvider.studentsCount}', icon: Icons.groups_rounded, color: AppTheme.purple),
                  _StatBox(label: 'Haziri', value: '${teacherProvider.stats['average_attendance'] ?? '96%'}', icon: Icons.how_to_reg_rounded, color: AppTheme.green),
                  _StatBox(label: 'Courses', value: '${teacherProvider.teacherClasses.length}', icon: Icons.history_edu_rounded, color: AppTheme.orange),
                  _StatBox(label: 'Rating', value: '4.9 ⭐', icon: Icons.star_rounded, color: const Color(0xFFF59E0B)),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Faculty Utilities (सुविधाहरू & Perks)
            const SectionHeader(
              title: 'Faculty Utilities & Records (सुविधाहरू)',
              subtitle: 'Leaves, Payslips & Timetable tools',
            ),
            const SizedBox(height: 10),
            Row(
              children: [
                Expanded(
                  child: _buildFacultyToolCard(
                    title: 'Leave Balance',
                    subtitle: '14 Days Available',
                    icon: Icons.event_available_rounded,
                    color: AppTheme.purple,
                    isDark: isDark,
                    onTap: () => _showLeaveBalanceDialog(context, isDark),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: _buildFacultyToolCard(
                    title: 'Salary & Payslip',
                    subtitle: 'रु. ५८,५०० (Ashwin)',
                    icon: Icons.receipt_long_rounded,
                    color: AppTheme.green,
                    isDark: isDark,
                    onTap: () => _showSalaryDialog(context, isDark),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            Row(
              children: [
                Expanded(
                  child: _buildFacultyToolCard(
                    title: 'Academic Calendar',
                    subtitle: '२०८१ B.S. Holidays',
                    icon: Icons.calendar_month_rounded,
                    color: AppTheme.primary,
                    isDark: isDark,
                    onTap: () => _showCalendarDialog(context, isDark),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: _buildFacultyToolCard(
                    title: 'Syllabus Tracker',
                    subtitle: '78% Course Finished',
                    icon: Icons.pie_chart_outline_rounded,
                    color: AppTheme.orange,
                    isDark: isDark,
                    onTap: () => _showSyllabusDialog(context, isDark),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 22),

            // App Settings & Preferences
            const SectionHeader(
              title: 'Settings & Security (सेटिङ्स)',
              subtitle: 'Preferences, language and session',
            ),
            const SizedBox(height: 10),
            AppCard(
              padding: EdgeInsets.zero,
              child: Column(
                children: [
                  _SettingsTile(
                    icon: Icons.person_outline_rounded,
                    label: 'Edit Teacher Profile',
                    subtitle: 'Phone, Subject, Degree credentials',
                    onTap: () {},
                  ),
                  _SettingsTile(
                    icon: isDark ? Icons.dark_mode_outlined : Icons.light_mode_outlined,
                    label: 'Dark Mode (रातीको मोड)',
                    subtitle: isDark ? 'Currently Dark Theme' : 'Currently Light Theme',
                    trailing: Switch(
                      value: isDark,
                      onChanged: (_) => themeProvider.toggle(),
                      activeColor: AppTheme.primary,
                      materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                    ),
                    onTap: () => themeProvider.toggle(),
                  ),
                  _SettingsTile(
                    icon: Icons.notifications_active_outlined,
                    label: 'Attendance & Push Alerts',
                    subtitle: 'Get notified for substitute periods',
                    trailing: Switch(
                      value: true,
                      onChanged: (_) {},
                      activeColor: AppTheme.primary,
                      materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                    ),
                    onTap: () {},
                  ),
                  _SettingsTile(
                    icon: Icons.language_rounded,
                    label: 'App Language',
                    value: 'English (नेपाली)',
                    onTap: () {},
                  ),
                  _SettingsTile(
                    icon: Icons.sync_rounded,
                    label: 'Nepal School Hub Cloud Sync',
                    value: 'Online (MIS Sync)',
                    onTap: () {},
                  ),
                  _SettingsTile(
                    icon: Icons.help_outline_rounded,
                    label: 'Faculty Helpdesk & Support',
                    subtitle: 'Hotline: 01-5970000',
                    onTap: () {},
                  ),
                  _SettingsTile(
                    icon: Icons.logout_rounded,
                    label: 'Sign Out (बाहिर निस्कनुहोस्)',
                    color: AppTheme.red,
                    showDivider: false,
                    onTap: () => _showSignOutDialog(context, authProvider),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFacultyToolCard({
    required String title,
    required String subtitle,
    required IconData icon,
    required Color color,
    required bool isDark,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: isDark ? AppTheme.darkCard : Colors.white,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(
            color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
            width: 1,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(isDark ? 0.2 : 0.03),
              blurRadius: 8,
              offset: const Offset(0, 3),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Container(
                  width: 38,
                  height: 38,
                  decoration: BoxDecoration(
                    color: color.withOpacity(0.12),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(icon, color: color, size: 20),
                ),
                Icon(
                  Icons.arrow_forward_ios_rounded,
                  size: 12,
                  color: AppTheme.darkSubtext.withOpacity(0.7),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Text(
              title,
              style: const TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w800,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              subtitle,
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

  void _showLeaveBalanceDialog(BuildContext context, bool isDark) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: isDark ? AppTheme.darkCard : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => Padding(
        padding: const EdgeInsets.all(22),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.event_available_rounded, color: AppTheme.purple, size: 24),
                SizedBox(width: 10),
                Text(
                  'Leave Balance २०८१ (बिदा विवरण)',
                  style: TextStyle(fontSize: 17, fontWeight: FontWeight.w900),
                ),
              ],
            ),
            const SizedBox(height: 18),
            _buildLeaveRow('Casual Leave (आकस्मिक विदा)', '6 / 12 Days remaining', 0.5, AppTheme.primary),
            const SizedBox(height: 12),
            _buildLeaveRow('Sick Leave (बिरामी विदा)', '8 / 12 Days remaining', 0.66, AppTheme.green),
            const SizedBox(height: 12),
            _buildLeaveRow('Earned / Festival Leave (पर्व विदा)', '14 / 20 Days remaining', 0.7, AppTheme.orange),
            const SizedBox(height: 20),
            SizedBox(
              width: double.infinity,
              height: 46,
              child: ElevatedButton(
                onPressed: () {
                  Navigator.pop(ctx);
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('Leave application form opened in Nepal School Hub Staff Portal'),
                    ),
                  );
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.purple,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: const Text('Apply for Leave (विदा निवेदन)', style: TextStyle(fontWeight: FontWeight.w800)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  static Widget _buildLeaveRow(String title, String status, double progress, Color color) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(title, style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w800)),
            Text(status, style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, color: color)),
          ],
        ),
        const SizedBox(height: 6),
        LinearProgressIndicator(
          value: progress,
          backgroundColor: color.withOpacity(0.15),
          valueColor: AlwaysStoppedAnimation<Color>(color),
          minHeight: 6,
          borderRadius: BorderRadius.circular(4),
        ),
      ],
    );
  }

  void _showSalaryDialog(BuildContext context, bool isDark) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: isDark ? AppTheme.darkCard : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => Padding(
        padding: const EdgeInsets.all(22),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.receipt_long_rounded, color: AppTheme.green, size: 24),
                SizedBox(width: 10),
                Text(
                  'Monthly Salary Payslip (तलब विवरण)',
                  style: TextStyle(fontSize: 17, fontWeight: FontWeight.w900),
                ),
              ],
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                borderRadius: BorderRadius.circular(14),
              ),
              child: const Column(
                children: [
                  _PayslipItem(label: 'Basic Salary', amount: 'रु. ४५,०००'),
                  _PayslipItem(label: 'Subject Head Allowance', amount: 'रु. ८,०००'),
                  _PayslipItem(label: 'Dearness & Transport Allowance', amount: 'रु. ७,५००'),
                  _PayslipItem(label: 'Provident Fund (CIT / SSF) (-)', amount: '- रु. २,०००', isNegative: true),
                  Divider(),
                  _PayslipItem(label: 'Net Payable Salary (Ashwin)', amount: 'रु. ५८,५००', isTotal: true),
                ],
              ),
            ),
            const SizedBox(height: 18),
            SizedBox(
              width: double.infinity,
              height: 46,
              child: ElevatedButton.icon(
                onPressed: () {
                  Navigator.pop(ctx);
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      backgroundColor: AppTheme.green,
                      content: Text('Salary Payslip PDF downloaded successfully ✅'),
                    ),
                  );
                },
                icon: const Icon(Icons.download_rounded, size: 18),
                label: const Text('Download Official Payslip PDF', style: TextStyle(fontWeight: FontWeight.w800)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.green,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showCalendarDialog(BuildContext context, bool isDark) {
    showModalBottomSheet(
      context: context,
      backgroundColor: isDark ? AppTheme.darkCard : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => Padding(
        padding: const EdgeInsets.all(22),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.calendar_month_rounded, color: AppTheme.primary, size: 24),
                SizedBox(width: 10),
                Text(
                  'Upcoming Holidays २०८१',
                  style: TextStyle(fontSize: 17, fontWeight: FontWeight.w900),
                ),
              ],
            ),
            const SizedBox(height: 14),
            _buildHolidayItem('Dashain Vacation', 'Ashwin 14 - Kartik 01 (18 Days)', AppTheme.red),
            _buildHolidayItem('Tihar & Chhath Puja', 'Kartik 15 - Kartik 22 (8 Days)', AppTheme.orange),
            _buildHolidayItem('Winter Vacation', 'Poush 15 - Magh 02 (15 Days)', AppTheme.primary),
          ],
        ),
      ),
    );
  }

  static Widget _buildHolidayItem(String title, String dates, Color color) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: color.withOpacity(0.08),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(title, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13)),
          Text(dates, style: TextStyle(fontWeight: FontWeight.w700, fontSize: 11.5, color: color)),
        ],
      ),
    );
  }

  void _showSyllabusDialog(BuildContext context, bool isDark) {
    showModalBottomSheet(
      context: context,
      backgroundColor: isDark ? AppTheme.darkCard : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => Padding(
        padding: const EdgeInsets.all(22),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.pie_chart_outline_rounded, color: AppTheme.orange, size: 24),
                SizedBox(width: 10),
                Text(
                  'Course Coverage (पाठ्यक्रम प्रगति)',
                  style: TextStyle(fontSize: 17, fontWeight: FontWeight.w900),
                ),
              ],
            ),
            const SizedBox(height: 14),
            _buildLeaveRow('Compulsory Math (Grade 10 - A)', '9 / 12 Chapters (75%)', 0.75, AppTheme.primary),
            const SizedBox(height: 12),
            _buildLeaveRow('Optional Mathematics (Grade 10 - B)', '10 / 12 Chapters (83%)', 0.83, AppTheme.green),
            const SizedBox(height: 12),
            _buildLeaveRow('Advanced Calculus (Grade 12 - A)', '7 / 10 Chapters (70%)', 0.70, AppTheme.purple),
          ],
        ),
      ),
    );
  }

  void _showSignOutDialog(BuildContext context, AuthProvider authProvider) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.logout_rounded, color: AppTheme.red),
            SizedBox(width: 8),
            Text(
              'Sign Out (बाहिर निस्कनुहोस्)',
              style: TextStyle(fontWeight: FontWeight.w800, fontSize: 17),
            ),
          ],
        ),
        content: const Text(
          'Are you sure you want to sign out of Guru Teacher Portal (Nepal School Hub)?',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(context);
              await authProvider.logout();
              if (context.mounted) {
                Navigator.pushAndRemoveUntil(
                  context,
                  MaterialPageRoute(builder: (_) => const LoginScreen()),
                  (_) => false,
                );
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.red,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
            child: const Text('Sign Out'),
          ),
        ],
      ),
    );
  }
}

class _StatBox extends StatelessWidget {
  final String label, value;
  final IconData icon;
  final Color color;
  const _StatBox({
    required this.label,
    required this.value,
    required this.icon,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Icon(icon, color: color, size: 18),
        const SizedBox(height: 4),
        Text(
          value,
          style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w900),
        ),
        const SizedBox(height: 1),
        Text(
          label,
          style: const TextStyle(fontSize: 10, color: AppTheme.darkSubtext, fontWeight: FontWeight.w600),
        ),
      ],
    );
  }
}

class _SettingsTile extends StatelessWidget {
  final IconData icon;
  final String label;
  final String? subtitle;
  final VoidCallback onTap;
  final Color? color;
  final bool showDivider;
  final String? value;
  final Widget? trailing;

  const _SettingsTile({
    required this.icon,
    required this.label,
    this.subtitle,
    required this.onTap,
    this.color,
    this.showDivider = true,
    this.value,
    this.trailing,
  });

  @override
  Widget build(BuildContext context) {
    final c = color ?? Theme.of(context).textTheme.bodyLarge?.color;
    return Column(
      children: [
        ListTile(
          onTap: onTap,
          leading: Icon(icon, color: c, size: 22),
          title: Text(
            label,
            style: TextStyle(
              fontSize: 13.5,
              fontWeight: FontWeight.w700,
              color: c,
            ),
          ),
          subtitle: subtitle != null
              ? Text(
                  subtitle!,
                  style: const TextStyle(fontSize: 11, color: AppTheme.darkSubtext),
                )
              : null,
          trailing: trailing ??
              (value != null
                  ? Text(
                      value!,
                      style: const TextStyle(
                        fontSize: 11.5,
                        fontWeight: FontWeight.w700,
                        color: AppTheme.primary,
                      ),
                    )
                  : color == null
                      ? const Icon(
                          Icons.chevron_right_rounded,
                          color: AppTheme.darkSubtext,
                          size: 20,
                        )
                      : null),
          contentPadding:
              const EdgeInsets.symmetric(horizontal: 16, vertical: 2),
        ),
        if (showDivider) const Divider(height: 1, indent: 56, endIndent: 16),
      ],
    );
  }
}

class _PayslipItem extends StatelessWidget {
  final String label, amount;
  final bool isNegative, isTotal;
  const _PayslipItem({
    required this.label,
    required this.amount,
    this.isNegative = false,
    this.isTotal = false,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: TextStyle(
              fontSize: isTotal ? 13.5 : 12,
              fontWeight: isTotal ? FontWeight.w900 : FontWeight.w600,
            ),
          ),
          Text(
            amount,
            style: TextStyle(
              fontSize: isTotal ? 14 : 12,
              fontWeight: FontWeight.w900,
              color: isNegative
                  ? AppTheme.red
                  : (isTotal ? AppTheme.green : null),
            ),
          ),
        ],
      ),
    );
  }
}
