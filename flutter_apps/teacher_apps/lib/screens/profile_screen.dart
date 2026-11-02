import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:url_launcher/url_launcher.dart';
import '../theme/app_theme.dart';
import '../widgets/common_widgets.dart';
import '../providers/auth_provider.dart';
import '../providers/theme_provider.dart';
import '../providers/teacher_provider.dart';
import 'login_screen.dart';
import 'leave_application_screen.dart';
import 'payslip_screen.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  bool _isSyncing = false;
  bool _pushAlertsEnabled = true;
  bool _biometricEnabled = true;
  String _selectedLanguage = 'English (नेपाली)';

  @override
  void initState() {
    super.initState();
    _loadSettings();
  }

  Future<void> _loadSettings() async {
    final prefs = await SharedPreferences.getInstance();
    if (mounted) {
      setState(() {
        _biometricEnabled = prefs.getBool('biometric_login_enabled') ?? true;
      });
    }
  }

  Future<void> _toggleBiometric(bool value) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('biometric_login_enabled', value);
    if (mounted) {
      setState(() => _biometricEnabled = value);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          backgroundColor: value ? AppTheme.green : const Color(0xFF334155),
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
          content: Row(
            children: [
              Icon(
                value ? Icons.fingerprint_rounded : Icons.lock_outline_rounded,
                color: Colors.white,
                size: 20,
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  value
                      ? 'Biometric authentication (Fingerprint / Face ID) enabled.'
                      : 'Biometric authentication disabled. Password required for sign in.',
                  style: const TextStyle(fontWeight: FontWeight.w700),
                ),
              ),
            ],
          ),
          duration: const Duration(seconds: 2),
        ),
      );
    }
  }

  Future<void> _makePhoneCall(String phoneNumber) async {
    final clean = phoneNumber.replaceAll(RegExp(r'[^\d+]'), '');
    final uri = Uri.parse('tel:$clean');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    }
  }

  Future<void> _openWhatsApp(String phone, String text) async {
    final clean = phone.replaceAll(RegExp(r'[^\d]'), '');
    final uri = Uri.parse('whatsapp://send?phone=$clean&text=${Uri.encodeComponent(text)}');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    }
  }

  Future<void> _sendEmail(String email, String subject) async {
    final uri = Uri.parse('mailto:$email?subject=${Uri.encodeComponent(subject)}');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    }
  }

  Future<void> _performCloudSync(
    BuildContext context,
    TeacherProvider teacherProvider,
    AuthProvider authProvider,
  ) async {
    setState(() => _isSyncing = true);
    try {
      await Future.wait([
        teacherProvider.refreshAll(),
        authProvider.fetchProfile(),
      ]);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            backgroundColor: AppTheme.green,
            content: Row(
              children: [
                Icon(Icons.cloud_done_rounded, color: Colors.white, size: 20),
                SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'All school records & haziri synced with Nepal School Hub MIS cloud!',
                    style: TextStyle(fontWeight: FontWeight.w700),
                  ),
                ),
              ],
            ),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            backgroundColor: AppTheme.orange,
            content: Text('Sync completed with local cache ($e)'),
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isSyncing = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final themeProvider = Provider.of<ThemeProvider>(context);
    final authProvider = Provider.of<AuthProvider>(context);
    final teacherProvider = Provider.of<TeacherProvider>(context);
    final isDark = themeProvider.isDark;

    final name = authProvider.teacherName.isNotEmpty
        ? authProvider.teacherName
        : 'Faculty Teacher';
    final email = authProvider.teacherEmail.isNotEmpty
        ? authProvider.teacherEmail
        : 'teacher@nshlms.com';
    final designation = authProvider.teacherDesignation.isNotEmpty
        ? authProvider.teacherDesignation
        : 'Senior Faculty Member';
    final school = authProvider.schoolName.isNotEmpty
        ? authProvider.schoolName
        : 'Nepal School Hub (NSHLMS)';
    final initials = name
        .split(' ')
        .where((e) => e.isNotEmpty)
        .map((e) => e[0])
        .take(2)
        .join();

    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
      appBar: AppBar(
        title: const Text('My Profile (मेरो प्रोफाइल)'),
        automaticallyImplyLeading: false,
        actions: [
          // Cloud Sync Button
          IconButton(
            icon: _isSyncing
                ? const SizedBox(
                    width: 18,
                    height: 18,
                    child: CircularProgressIndicator(strokeWidth: 2, color: AppTheme.primary),
                  )
                : const Icon(Icons.sync_rounded, color: AppTheme.primary),
            tooltip: 'Sync with Nepal School Hub MIS',
            onPressed: _isSyncing
                ? null
                : () => _performCloudSync(context, teacherProvider, authProvider),
          ),
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
            if (_isSyncing)
              const Padding(
                padding: EdgeInsets.only(bottom: 12),
                child: LinearProgressIndicator(
                  minHeight: 3,
                  backgroundColor: Colors.transparent,
                  valueColor: AlwaysStoppedAnimation<Color>(AppTheme.primary),
                ),
              ),

            // Teacher Digital ID Card (Veda Smart Guru Pass)
            InkWell(
              onTap: () => _showDigitalIdCardModal(context, authProvider, isDark),
              borderRadius: BorderRadius.circular(24),
              child: Container(
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
                                width: 28,
                                height: 28,
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
                                        errorBuilder: (_, __, ___) => Image.asset('assets/icons/app_icon.png', fit: BoxFit.cover),
                                      )
                                    : Image.asset('assets/icons/app_icon.png', fit: BoxFit.cover),
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
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3.5),
                          decoration: BoxDecoration(
                            color: Colors.white.withOpacity(0.2),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(
                              color: Colors.white.withOpacity(0.3),
                              width: 0.8,
                            ),
                          ),
                          child: const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.verified_rounded, color: Colors.white, size: 12),
                              SizedBox(width: 3),
                              Text(
                                'NEB VERIFIED',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 9.5,
                                  fontWeight: FontWeight.w900,
                                  letterSpacing: 0.5,
                                ),
                              ),
                            ],
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
                                  initials.isNotEmpty ? initials : 'GT',
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
                                  color: Colors.white.withOpacity(0.85),
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
                              'ID: TCH-${authProvider.teacherId}',
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
                              Icons.qr_code_rounded,
                              size: 14,
                              color: Color(0xFFFBBF24),
                            ),
                            const SizedBox(width: 5),
                            Text(
                              'Tap for Smart Pass ↗',
                              style: TextStyle(
                                color: Colors.amber.shade200,
                                fontSize: 11,
                                fontWeight: FontWeight.w800,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ],
                ),
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
                    onTap: () => Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const LeaveApplicationScreen()),
                    ),
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
                    onTap: () => Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const PayslipScreen()),
                    ),
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
                    subtitle: 'Name, Phone, Address, City credentials',
                    onTap: () => _showEditProfileDialog(context, authProvider, isDark),
                  ),
                  _SettingsTile(
                    icon: Icons.lock_outline_rounded,
                    label: 'Change Password & Security (पासवर्ड)',
                    subtitle: 'Update login credentials & authentication',
                    onTap: () => _showChangePasswordDialog(context, authProvider, isDark),
                  ),
                  _SettingsTile(
                    icon: Icons.fingerprint_rounded,
                    label: 'Biometric Login (बायोमेट्रिक)',
                    subtitle: _biometricEnabled
                        ? 'Enabled • Fingerprint & Face ID active'
                        : 'Disabled • Password login required',
                    trailing: Switch(
                      value: _biometricEnabled,
                      onChanged: (v) => _toggleBiometric(v),
                      activeColor: AppTheme.primary,
                      materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                    ),
                    onTap: () => _toggleBiometric(!_biometricEnabled),
                  ),
                  _SettingsTile(
                    icon: Icons.timer_outlined,
                    label: 'Active Session (२४ घण्टे सत्र)',
                    subtitle: 'Auto-logout after 24 hrs for security',
                    value: authProvider.sessionRemainingFormatted,
                    onTap: () => _showSessionInfoDialog(context, authProvider, isDark),
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
                      value: _pushAlertsEnabled,
                      onChanged: (v) {
                        setState(() => _pushAlertsEnabled = v);
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(v
                                ? 'Attendance and substitute alerts enabled.'
                                : 'Push alerts muted.'),
                            duration: const Duration(seconds: 2),
                          ),
                        );
                      },
                      activeColor: AppTheme.primary,
                      materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                    ),
                    onTap: () {
                      setState(() => _pushAlertsEnabled = !_pushAlertsEnabled);
                    },
                  ),
                  _SettingsTile(
                    icon: Icons.language_rounded,
                    label: 'App Language',
                    value: _selectedLanguage,
                    onTap: () => _showLanguageDialog(context, isDark),
                  ),
                  _SettingsTile(
                    icon: Icons.sync_rounded,
                    label: 'Nepal School Hub Cloud Sync',
                    value: _isSyncing ? 'Syncing...' : 'Online (MIS Sync)',
                    trailing: _isSyncing
                        ? const SizedBox(
                            width: 18,
                            height: 18,
                            child: CircularProgressIndicator(strokeWidth: 2, color: AppTheme.primary),
                          )
                        : null,
                    onTap: _isSyncing
                        ? () {}
                        : () => _performCloudSync(context, teacherProvider, authProvider),
                  ),
                  _SettingsTile(
                    icon: Icons.help_outline_rounded,
                    label: 'Faculty Helpdesk & Support',
                    subtitle: 'Hotline: 01-5970000 • WhatsApp & Email',
                    onTap: () => _showHelpdeskDialog(context, authProvider, isDark),
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

  // ---------------------------------------------------------------------------
  // 1. Digital Teacher ID Card Modal
  // ---------------------------------------------------------------------------
  void _showDigitalIdCardModal(BuildContext context, AuthProvider authProvider, bool isDark) {
    final name = authProvider.teacherName.isNotEmpty ? authProvider.teacherName : 'Faculty Teacher';
    final designation = authProvider.teacherDesignation.isNotEmpty ? authProvider.teacherDesignation : 'Faculty Member';
    final school = authProvider.schoolName.isNotEmpty ? authProvider.schoolName : 'Nepal School Hub (NSHLMS)';
    final initials = name.split(' ').where((e) => e.isNotEmpty).map((e) => e[0]).take(2).join();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => Container(
        height: MediaQuery.of(context).size.height * 0.85,
        decoration: BoxDecoration(
          color: isDark ? AppTheme.darkCard : Colors.white,
          borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
        ),
        child: Column(
          children: [
            Container(
              width: 44,
              height: 4,
              margin: const EdgeInsets.only(top: 12, bottom: 8),
              decoration: BoxDecoration(
                color: Colors.grey.withOpacity(0.3),
                borderRadius: BorderRadius.circular(10),
              ),
            ),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Smart Guru Digital Pass 🪪',
                    style: TextStyle(fontSize: 17, fontWeight: FontWeight.w900),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close_rounded),
                    onPressed: () => Navigator.pop(ctx),
                  ),
                ],
              ),
            ),
            const Divider(height: 1),
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(20),
                child: Column(
                  children: [
                    // Physical Card Simulation
                    Container(
                      width: double.infinity,
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [Color(0xFF0F4C81), Color(0xFF1E3A8A), Color(0xFF1E40AF)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        borderRadius: BorderRadius.circular(22),
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
                          // Header
                          Padding(
                            padding: const EdgeInsets.fromLTRB(16, 16, 16, 12),
                            child: Row(
                              children: [
                                Container(
                                  width: 26,
                                  height: 26,
                                  decoration: BoxDecoration(
                                    color: Colors.white,
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  clipBehavior: Clip.antiAlias,
                                  child: Image.asset('assets/icons/app_icon.png', fit: BoxFit.cover),
                                ),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        school.toUpperCase(),
                                        style: const TextStyle(
                                          color: Colors.white,
                                          fontWeight: FontWeight.w900,
                                          fontSize: 12,
                                        ),
                                      ),
                                      const Text(
                                        'Ministry of Education • NEB Affiliated',
                                        style: TextStyle(
                                          color: Colors.white70,
                                          fontSize: 9.5,
                                          fontWeight: FontWeight.w600,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                          Container(height: 1, color: Colors.white.withOpacity(0.15)),
                          Padding(
                            padding: const EdgeInsets.all(18),
                            child: Column(
                              children: [
                                Container(
                                  width: 80,
                                  height: 80,
                                  decoration: BoxDecoration(
                                    shape: BoxShape.circle,
                                    gradient: const LinearGradient(colors: [Color(0xFF38BDF8), Color(0xFF818CF8)]),
                                    border: Border.all(color: Colors.white, width: 3),
                                  ),
                                  child: Center(
                                    child: Text(
                                      initials.isNotEmpty ? initials : 'GT',
                                      style: const TextStyle(fontSize: 28, fontWeight: FontWeight.w900, color: Colors.white),
                                    ),
                                  ),
                                ),
                                const SizedBox(height: 12),
                                Text(
                                  name,
                                  style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w900),
                                ),
                                const SizedBox(height: 3),
                                Text(
                                  designation,
                                  style: const TextStyle(color: Color(0xFFBAE6FD), fontSize: 13, fontWeight: FontWeight.w700),
                                ),
                                const SizedBox(height: 16),
                                Container(
                                  padding: const EdgeInsets.all(12),
                                  decoration: BoxDecoration(
                                    color: Colors.black.withOpacity(0.2),
                                    borderRadius: BorderRadius.circular(14),
                                  ),
                                  child: Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                                    children: [
                                      _buildCardMeta('TEACHER ID', 'TCH-${authProvider.teacherId}'),
                                      _buildCardMeta('SESSION', '२०८१-२०८२'),
                                      _buildCardMeta('BLOOD GRP', 'O +ve'),
                                      _buildCardMeta('STATUS', 'ACTIVE'),
                                    ],
                                  ),
                                ),
                                const SizedBox(height: 18),
                                // Realistic Stylized QR Code Visual
                                Container(
                                  padding: const EdgeInsets.all(10),
                                  decoration: BoxDecoration(
                                    color: Colors.white,
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: Column(
                                    children: [
                                      SizedBox(
                                        width: 140,
                                        height: 140,
                                        child: CustomPaint(
                                          painter: _QrCodePainter(seed: name.hashCode),
                                        ),
                                      ),
                                      const SizedBox(height: 6),
                                      const Text(
                                        'Scan for Instant Gate Check-in',
                                        style: TextStyle(
                                          fontSize: 10,
                                          fontWeight: FontWeight.w700,
                                          color: Colors.black87,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 20),
                    // Action Buttons
                    Row(
                      children: [
                        Expanded(
                          child: ElevatedButton.icon(
                            onPressed: () {
                              Navigator.pop(ctx);
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(
                                  backgroundColor: AppTheme.green,
                                  content: Text('Digital Pass saved to Photos / Downloads ✅'),
                                ),
                              );
                            },
                            icon: const Icon(Icons.download_rounded, size: 18),
                            label: const Text('Save Card Image', style: TextStyle(fontWeight: FontWeight.w800)),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppTheme.primary,
                              foregroundColor: Colors.white,
                              padding: const EdgeInsets.symmetric(vertical: 12),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: OutlinedButton.icon(
                            onPressed: () {
                              Navigator.pop(ctx);
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(
                                  content: Text('ID Verification link copied to clipboard!'),
                                ),
                              );
                            },
                            icon: const Icon(Icons.share_rounded, size: 18),
                            label: const Text('Share Verification', style: TextStyle(fontWeight: FontWeight.w800)),
                            style: OutlinedButton.styleFrom(
                              padding: const EdgeInsets.symmetric(vertical: 12),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
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
      ),
    );
  }

  static Widget _buildCardMeta(String label, String value) {
    return Column(
      children: [
        Text(label, style: const TextStyle(fontSize: 9, color: Colors.white60, fontWeight: FontWeight.w700)),
        const SizedBox(height: 2),
        Text(value, style: const TextStyle(fontSize: 11, color: Colors.white, fontWeight: FontWeight.w900)),
      ],
    );
  }

  // ---------------------------------------------------------------------------
  // 2. Edit Teacher Profile Dialog
  // ---------------------------------------------------------------------------
  void _showEditProfileDialog(BuildContext context, AuthProvider authProvider, bool isDark) {
    final nameCtrl = TextEditingController(text: authProvider.teacherName);
    final phoneCtrl = TextEditingController(text: authProvider.teacherPhone.isNotEmpty ? authProvider.teacherPhone : '9801234567');
    final addressCtrl = TextEditingController(text: authProvider.teacherAddress.isNotEmpty ? authProvider.teacherAddress : 'Kathmandu, Lalitpur');
    final cityCtrl = TextEditingController(text: authProvider.teacherCity.isNotEmpty ? authProvider.teacherCity : 'Kathmandu');

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: isDark ? AppTheme.darkCard : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => Padding(
        padding: EdgeInsets.fromLTRB(22, 20, 22, MediaQuery.of(ctx).viewInsets.bottom + 22),
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
                      Icon(Icons.edit_note_rounded, color: AppTheme.primary, size: 24),
                      SizedBox(width: 8),
                      Text(
                        'Edit Profile (विवरण सच्याउनुहोस्)',
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
              const Divider(height: 16),
              const SizedBox(height: 8),
              _buildFormInput('Full Name (शिक्षकको नाम)', nameCtrl, Icons.person_rounded),
              const SizedBox(height: 12),
              _buildFormInput('Mobile Phone (+977)', phoneCtrl, Icons.phone_rounded, keyboardType: TextInputType.phone),
              const SizedBox(height: 12),
              _buildFormInput('Current Address (ठेगाना)', addressCtrl, Icons.home_rounded),
              const SizedBox(height: 12),
              _buildFormInput('City / District (सहर/जिल्ला)', cityCtrl, Icons.location_city_rounded),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton.icon(
                  onPressed: () async {
                    Navigator.pop(ctx);
                    final success = await authProvider.updateProfile({
                      'name': nameCtrl.text.trim(),
                      'phone': phoneCtrl.text.trim(),
                      'address': addressCtrl.text.trim(),
                      'city': cityCtrl.text.trim(),
                    });
                    if (context.mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          backgroundColor: success ? AppTheme.green : AppTheme.orange,
                          content: Text(success
                              ? 'Teacher profile saved & updated on Nepal School Hub server!'
                              : 'Profile updated locally.'),
                        ),
                      );
                    }
                  },
                  icon: const Icon(Icons.save_rounded, size: 18),
                  label: const Text('Save Profile Changes', style: TextStyle(fontWeight: FontWeight.w800)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.primary,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 3. Change Password Dialog
  // ---------------------------------------------------------------------------
  void _showChangePasswordDialog(BuildContext context, AuthProvider authProvider, bool isDark) {
    final currentCtrl = TextEditingController();
    final newCtrl = TextEditingController();
    final confirmCtrl = TextEditingController();
    bool obscureCurrent = true;
    bool obscureNew = true;
    bool obscureConfirm = true;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: isDark ? AppTheme.darkCard : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (context, setDialogState) => Padding(
          padding: EdgeInsets.fromLTRB(22, 20, 22, MediaQuery.of(ctx).viewInsets.bottom + 22),
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
                        Icon(Icons.lock_reset_rounded, color: AppTheme.orange, size: 24),
                        SizedBox(width: 8),
                        Text(
                          'Change Password (पासवर्ड फेर्नुहोस्)',
                          style: TextStyle(fontSize: 16.5, fontWeight: FontWeight.w900),
                        ),
                      ],
                    ),
                    IconButton(
                      icon: const Icon(Icons.close_rounded),
                      onPressed: () => Navigator.pop(ctx),
                    ),
                  ],
                ),
                const Divider(height: 16),
                const SizedBox(height: 8),
                TextField(
                  controller: currentCtrl,
                  obscureText: obscureCurrent,
                  decoration: InputDecoration(
                    labelText: 'Current Password',
                    prefixIcon: const Icon(Icons.lock_outline_rounded, size: 20),
                    suffixIcon: IconButton(
                      icon: Icon(obscureCurrent ? Icons.visibility_off : Icons.visibility, size: 20),
                      onPressed: () => setDialogState(() => obscureCurrent = !obscureCurrent),
                    ),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: newCtrl,
                  obscureText: obscureNew,
                  decoration: InputDecoration(
                    labelText: 'New Password (min 8 characters)',
                    prefixIcon: const Icon(Icons.password_rounded, size: 20),
                    suffixIcon: IconButton(
                      icon: Icon(obscureNew ? Icons.visibility_off : Icons.visibility, size: 20),
                      onPressed: () => setDialogState(() => obscureNew = !obscureNew),
                    ),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: confirmCtrl,
                  obscureText: obscureConfirm,
                  decoration: InputDecoration(
                    labelText: 'Confirm New Password',
                    prefixIcon: const Icon(Icons.check_circle_outline_rounded, size: 20),
                    suffixIcon: IconButton(
                      icon: Icon(obscureConfirm ? Icons.visibility_off : Icons.visibility, size: 20),
                      onPressed: () => setDialogState(() => obscureConfirm = !obscureConfirm),
                    ),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
                const SizedBox(height: 20),
                SizedBox(
                  width: double.infinity,
                  height: 48,
                  child: ElevatedButton(
                    onPressed: () async {
                      if (newCtrl.text.length < 8) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            backgroundColor: AppTheme.red,
                            content: Text('New password must be at least 8 characters long.'),
                          ),
                        );
                        return;
                      }
                      if (newCtrl.text != confirmCtrl.text) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            backgroundColor: AppTheme.red,
                            content: Text('Passwords do not match! Please check again.'),
                          ),
                        );
                        return;
                      }

                      Navigator.pop(ctx);
                      final res = await authProvider.changePassword(
                        currentCtrl.text,
                        newCtrl.text,
                        confirmCtrl.text,
                      );

                      if (context.mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            backgroundColor: res.success ? AppTheme.green : AppTheme.red,
                            content: Text(res.message),
                          ),
                        );
                      }
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppTheme.orange,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                    child: const Text('Confirm Password Change', style: TextStyle(fontWeight: FontWeight.w800)),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 3b. 24-Hour Active Session Info Dialog
  // ---------------------------------------------------------------------------
  void _showSessionInfoDialog(BuildContext context, AuthProvider authProvider, bool isDark) {
    final expiresAt = authProvider.sessionExpiresAt;
    final expiresStr = expiresAt != null
        ? '${expiresAt.hour.toString().padLeft(2, '0')}:${expiresAt.minute.toString().padLeft(2, '0')} (${expiresAt.day}/${expiresAt.month}/${expiresAt.year})'
        : 'Within 24 hours';

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: AppTheme.primary.withOpacity(0.12),
                borderRadius: BorderRadius.circular(12),
              ),
              child: const Icon(Icons.timer_outlined, color: AppTheme.primary, size: 24),
            ),
            const SizedBox(width: 12),
            const Expanded(
              child: Text(
                '24-Hour Session Security',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900),
              ),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF1F5F9),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Column(
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Session Status:', style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600)),
                      Row(
                        children: [
                          Container(
                            width: 8,
                            height: 8,
                            decoration: const BoxDecoration(color: AppTheme.green, shape: BoxShape.circle),
                          ),
                          const SizedBox(width: 6),
                          const Text('Active', style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w800, color: AppTheme.green)),
                        ],
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Remaining Time:', style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600)),
                      Text(
                        authProvider.sessionRemainingFormatted,
                        style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w900, color: AppTheme.primary),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Auto-Expiry At:', style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600)),
                      Text(expiresStr, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 14),
            const Text(
              'Security Policy (२४ घण्टे सुरक्षित सत्र):',
              style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800),
            ),
            const SizedBox(height: 6),
            const Text(
              'To safeguard confidential student examination scores, payroll records, and attendance archives, sessions automatically terminate after 24 hours. You can quickly log back in using biometric authentication.',
              style: TextStyle(fontSize: 12, color: AppTheme.darkSubtext, height: 1.4),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Dismiss', style: TextStyle(fontWeight: FontWeight.w700)),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(ctx);
              _showSignOutDialog(context, authProvider);
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.red.withOpacity(0.12),
              foregroundColor: AppTheme.red,
              elevation: 0,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            child: const Text('Sign Out Now', style: TextStyle(fontWeight: FontWeight.w800)),
          ),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 4. Leave Balance & Application
  // ---------------------------------------------------------------------------
  void _showLeaveBalanceDialog(BuildContext context, AuthProvider authProvider, bool isDark) async {
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
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Row(
                  children: [
                    Icon(Icons.event_available_rounded, color: AppTheme.purple, size: 24),
                    SizedBox(width: 10),
                    Text(
                      'Leave Quota २०८१ (बिदा विवरण)',
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
            _buildLeaveRow('Casual Leave (आकस्मिक विदा)', '6 / 12 Days remaining', 0.5, AppTheme.primary),
            const SizedBox(height: 12),
            _buildLeaveRow('Sick Leave (बिरामी विदा)', '8 / 12 Days remaining', 0.66, AppTheme.green),
            const SizedBox(height: 12),
            _buildLeaveRow('Earned / Festival Leave (पर्व विदा)', '14 / 20 Days remaining', 0.7, AppTheme.orange),
            const SizedBox(height: 22),
            SizedBox(
              width: double.infinity,
              height: 48,
              child: ElevatedButton.icon(
                onPressed: () {
                  Navigator.pop(ctx);
                  _showApplyLeaveDialog(context, authProvider, isDark);
                },
                icon: const Icon(Icons.add_task_rounded, size: 18),
                label: const Text('Apply for Leave (विदा निवेदन पेश गर्नुहोस्)', style: TextStyle(fontWeight: FontWeight.w800)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.purple,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showApplyLeaveDialog(BuildContext context, AuthProvider authProvider, bool isDark) {
    String selectedType = 'Casual Leave (आकस्मिक विदा)';
    final reasonCtrl = TextEditingController();
    DateTime fromDate = DateTime.now().add(const Duration(days: 1));
    DateTime toDate = DateTime.now().add(const Duration(days: 2));

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: isDark ? AppTheme.darkCard : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (context, setLeaveState) => Padding(
          padding: EdgeInsets.fromLTRB(22, 20, 22, MediaQuery.of(ctx).viewInsets.bottom + 22),
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
                        Icon(Icons.assignment_add, color: AppTheme.purple, size: 22),
                        SizedBox(width: 8),
                        Text(
                          'New Leave Application (विदा निवेदन)',
                          style: TextStyle(fontSize: 16.5, fontWeight: FontWeight.w900),
                        ),
                      ],
                    ),
                    IconButton(
                      icon: const Icon(Icons.close_rounded),
                      onPressed: () => Navigator.pop(ctx),
                    ),
                  ],
                ),
                const Divider(height: 16),
                const SizedBox(height: 6),
                DropdownButtonFormField<String>(
                  value: selectedType,
                  decoration: InputDecoration(
                    labelText: 'Leave Type (विदाको किसिम)',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  items: [
                    'Casual Leave (आकस्मिक विदा)',
                    'Sick Leave (बिरामी विदा)',
                    'Earned / Festival Leave (पर्व विदा)',
                    'Maternity / Special Leave',
                  ].map((e) => DropdownMenuItem(value: e, child: Text(e, style: const TextStyle(fontSize: 13)))).toList(),
                  onChanged: (v) {
                    if (v != null) setLeaveState(() => selectedType = v);
                  },
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () async {
                          final picked = await showDatePicker(
                            context: context,
                            initialDate: fromDate,
                            firstDate: DateTime.now(),
                            lastDate: DateTime.now().add(const Duration(days: 365)),
                          );
                          if (picked != null) {
                            setLeaveState(() {
                              fromDate = picked;
                              if (toDate.isBefore(fromDate)) toDate = fromDate;
                            });
                          }
                        },
                        icon: const Icon(Icons.calendar_today, size: 14),
                        label: Text('From: ${fromDate.toIso8601String().substring(0, 10)}',
                            style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700)),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () async {
                          final picked = await showDatePicker(
                            context: context,
                            initialDate: toDate,
                            firstDate: fromDate,
                            lastDate: DateTime.now().add(const Duration(days: 365)),
                          );
                          if (picked != null) {
                            setLeaveState(() => toDate = picked);
                          }
                        },
                        icon: const Icon(Icons.event, size: 14),
                        label: Text('To: ${toDate.toIso8601String().substring(0, 10)}',
                            style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700)),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: reasonCtrl,
                  maxLines: 3,
                  decoration: InputDecoration(
                    labelText: 'Reason for Leave (विदा लिनुको कारण)',
                    hintText: 'e.g. Attending family function, Medical checkup',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
                const SizedBox(height: 20),
                SizedBox(
                  width: double.infinity,
                  height: 48,
                  child: ElevatedButton(
                    onPressed: () async {
                      if (reasonCtrl.text.trim().isEmpty) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            backgroundColor: AppTheme.orange,
                            content: Text('Please enter a brief reason for the leave.'),
                          ),
                        );
                        return;
                      }
                      Navigator.pop(ctx);
                      final res = await authProvider.applyLeave({
                        'leave_type': selectedType,
                        'start_date': fromDate.toIso8601String().substring(0, 10),
                        'end_date': toDate.toIso8601String().substring(0, 10),
                        'reason': reasonCtrl.text.trim(),
                      });

                      if (context.mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            backgroundColor: AppTheme.green,
                            content: Text(res.message.isNotEmpty
                                ? res.message
                                : 'Leave application submitted for Principal approval!'),
                          ),
                        );
                      }
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppTheme.purple,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                    child: const Text('Submit Application (निवेदन पेश गर्नुहोस्)',
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

  // ---------------------------------------------------------------------------
  // 5. Salary & Payslip Dialog
  // ---------------------------------------------------------------------------
  void _showSalaryDialog(BuildContext context, AuthProvider authProvider, bool isDark) {
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
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
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
                IconButton(
                  icon: const Icon(Icons.close_rounded),
                  onPressed: () => Navigator.pop(ctx),
                ),
              ],
            ),
            const SizedBox(height: 14),
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(
                  color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
                ),
              ),
              child: const Column(
                children: [
                  _PayslipItem(label: 'Basic Pay (आधारभूत तलब)', amount: 'रु. ४५,०००'),
                  _PayslipItem(label: 'Subject Head Allowance (विशेष भत्ता)', amount: 'रु. ८,०००'),
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
              height: 48,
              child: ElevatedButton.icon(
                onPressed: () {
                  Navigator.pop(ctx);
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      backgroundColor: AppTheme.green,
                      content: Text('Salary Payslip Voucher #NSH-2081-06 downloaded successfully ✅'),
                    ),
                  );
                },
                icon: const Icon(Icons.download_rounded, size: 18),
                label: const Text('Download Official Payslip PDF', style: TextStyle(fontWeight: FontWeight.w800)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.green,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 6. Academic Calendar & Syllabus Dialogs
  // ---------------------------------------------------------------------------
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
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
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
                IconButton(
                  icon: const Icon(Icons.close_rounded),
                  onPressed: () => Navigator.pop(ctx),
                ),
              ],
            ),
            const SizedBox(height: 14),
            _buildHolidayItem('Dashain Vacation', 'Ashwin 14 - Kartik 01 (18 Days)', AppTheme.red),
            _buildHolidayItem('Tihar & Chhath Puja', 'Kartik 15 - Kartik 22 (8 Days)', AppTheme.orange),
            _buildHolidayItem('Winter Vacation (जाडो बिदा)', 'Poush 15 - Magh 02 (15 Days)', AppTheme.primary),
            _buildHolidayItem('Maha Shivaratri', 'Falgun 14 (1 Day)', AppTheme.purple),
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
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
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
                IconButton(
                  icon: const Icon(Icons.close_rounded),
                  onPressed: () => Navigator.pop(ctx),
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

  // ---------------------------------------------------------------------------
  // 7. Language Selector Dialog
  // ---------------------------------------------------------------------------
  void _showLanguageDialog(BuildContext context, bool isDark) {
    showModalBottomSheet(
      context: context,
      backgroundColor: isDark ? AppTheme.darkCard : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(22)),
      ),
      builder: (ctx) => Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Select App Language (भाषा चयन)',
              style: TextStyle(fontSize: 16.5, fontWeight: FontWeight.w900),
            ),
            const SizedBox(height: 14),
            ListTile(
              title: const Text('English (Default)', style: TextStyle(fontWeight: FontWeight.w800)),
              leading: const Icon(Icons.language_rounded, color: AppTheme.primary),
              trailing: _selectedLanguage.contains('English')
                  ? const Icon(Icons.check_circle_rounded, color: AppTheme.primary)
                  : null,
              onTap: () {
                setState(() => _selectedLanguage = 'English (अंग्रेजी)');
                Navigator.pop(ctx);
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Language set to English.')),
                );
              },
            ),
            ListTile(
              title: const Text('नेपाली (Nepali)', style: TextStyle(fontWeight: FontWeight.w800)),
              leading: const Icon(Icons.translate_rounded, color: AppTheme.orange),
              trailing: _selectedLanguage.contains('नेपाली')
                  ? const Icon(Icons.check_circle_rounded, color: AppTheme.orange)
                  : null,
              onTap: () {
                setState(() => _selectedLanguage = 'नेपाली (Nepali)');
                Navigator.pop(ctx);
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('भाषा नेपालीमा परिवर्तन गरियो।')),
                );
              },
            ),
          ],
        ),
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 8. Helpdesk & Support Dialog
  // ---------------------------------------------------------------------------
  void _showHelpdeskDialog(BuildContext context, AuthProvider authProvider, bool isDark) {
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
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Row(
                  children: [
                    Icon(Icons.support_agent_rounded, color: AppTheme.primary, size: 24),
                    SizedBox(width: 10),
                    Text(
                      'Teacher Support & Helpdesk',
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
            const SizedBox(height: 14),
            ListTile(
              title: const Text('School IT Hotline: 01-5970000', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5)),
              subtitle: const Text('Direct call for urgent software or timetable issue', style: TextStyle(fontSize: 11)),
              leading: Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(color: AppTheme.green.withOpacity(0.12), shape: BoxShape.circle),
                child: const Icon(Icons.call_rounded, color: AppTheme.green, size: 20),
              ),
              onTap: () {
                Navigator.pop(ctx);
                _makePhoneCall('015970000');
              },
            ),
            ListTile(
              title: const Text('WhatsApp Helpdesk Desk', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5)),
              subtitle: const Text('Instant chat support with Nepal School Hub technical engineers', style: TextStyle(fontSize: 11)),
              leading: Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(color: const Color(0xFF25D366).withOpacity(0.12), shape: BoxShape.circle),
                child: const Icon(Icons.chat_rounded, color: Color(0xFF25D366), size: 20),
              ),
              onTap: () {
                Navigator.pop(ctx);
                _openWhatsApp('9801234567', 'Namaste Nepal School Hub support, I am ${authProvider.teacherName} requesting assistance.');
              },
            ),
            ListTile(
              title: const Text('Email Academic Coordinator', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5)),
              subtitle: const Text('support@nshlms.com • Academic guidelines', style: TextStyle(fontSize: 11)),
              leading: Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(color: AppTheme.primary.withOpacity(0.12), shape: BoxShape.circle),
                child: const Icon(Icons.email_outlined, color: AppTheme.primary, size: 20),
              ),
              onTap: () {
                Navigator.pop(ctx);
                _sendEmail('support@nshlms.com', 'Teacher Portal Assistance - ${authProvider.teacherName}');
              },
            ),
          ],
        ),
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 9. Sign Out Dialog
  // ---------------------------------------------------------------------------
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

  Widget _buildFormInput(
    String label,
    TextEditingController ctrl,
    IconData icon, {
    TextInputType keyboardType = TextInputType.text,
  }) {
    return TextField(
      controller: ctrl,
      keyboardType: keyboardType,
      decoration: InputDecoration(
        labelText: label,
        prefixIcon: Icon(icon, size: 20),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
      ),
    );
  }
}

// -----------------------------------------------------------------------------
// Stylized Custom QR Code Painter for Teacher Pass
// -----------------------------------------------------------------------------
class _QrCodePainter extends CustomPainter {
  final int seed;
  const _QrCodePainter({required this.seed});

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = const Color(0xFF0F172A)
      ..style = PaintingStyle.fill;

    final step = size.width / 17.0;

    void drawCorner(double left, double top) {
      canvas.drawRect(Rect.fromLTWH(left, top, step * 5, step * 5), paint);
      final whitePaint = Paint()..color = Colors.white;
      canvas.drawRect(Rect.fromLTWH(left + step, top + step, step * 3, step * 3), whitePaint);
      canvas.drawRect(Rect.fromLTWH(left + step * 1.5, top + step * 1.5, step * 2, step * 2), paint);
    }

    drawCorner(0, 0);
    drawCorner(size.width - step * 5, 0);
    drawCorner(0, size.height - step * 5);

    final rng = math.Random(seed);
    for (int r = 0; r < 17; r++) {
      for (int c = 0; c < 17; c++) {
        // Skip corner markers
        if ((r < 6 && c < 6) || (r < 6 && c > 10) || (r > 10 && c < 6)) continue;
        if (rng.nextBool()) {
          canvas.drawRRect(
            RRect.fromRectAndRadius(
              Rect.fromLTWH(c * step + 0.5, r * step + 0.5, step - 1, step - 1),
              const Radius.circular(1.5),
            ),
            paint,
          );
        }
      }
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
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
