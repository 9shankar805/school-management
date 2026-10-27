import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../providers/api_provider.dart';
import '../widgets/common_widgets.dart';
import '../main.dart';
import 'login_screen.dart';
import 'report_card_screen.dart';
import 'teacher_contact_screen.dart';
import 'meeting_screen.dart';
import 'events_screen.dart';
import 'health_screen.dart';
import 'behavior_screen.dart';
import 'leave_request_screen.dart';
import '../services/api_service.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  bool _notificationsEnabled = true;
  String _selectedLanguage = 'English';
  bool _biometricsEnabled = false;

  @override
  Widget build(BuildContext context) {
    final themeProvider = ParentsApp.of(context)?.themeProvider;
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final api = context.watch<ApiProvider>();
    final profile = api.userProfile;
    final children = api.children;

    final parentName = profile?['name'] ?? profile?['full_name'] ?? 'Parent Account';
    final parentEmail = profile?['email'] ?? 'parent@nepalschoolhub.com';
    final initials = parentName.isNotEmpty ? parentName[0].toUpperCase() : 'P';

    final double totalPending = api.fees
        .where((f) => f.status.toLowerCase() == 'pending')
        .fold(0.0, (sum, f) => sum + f.dueAmount);

    return Scaffold(
      appBar: AppBar(
        title: const Text('My Account & Settings'),
        automaticallyImplyLeading: false,
        actions: [
          IconButton(
            icon: Icon(isDark ? Icons.light_mode_rounded : Icons.dark_mode_rounded),
            tooltip: 'Toggle Theme',
            onPressed: () => themeProvider?.toggle(),
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 100),
        child: Column(
          children: [
            // Parent Profile Hero Card
            AppCard(
              padding: const EdgeInsets.all(20),
              child: Column(
                children: [
                  Stack(
                    alignment: Alignment.bottomRight,
                    children: [
                      Container(
                        width: 84,
                        height: 84,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          gradient: AppTheme.primaryGradient,
                          border: Border.all(color: AppTheme.primary, width: 3),
                        ),
                        child: Center(
                          child: Text(
                            initials,
                            style: const TextStyle(color: Colors.white, fontSize: 32, fontWeight: FontWeight.w900),
                          ),
                        ),
                      ),
                      GestureDetector(
                        onTap: () => _showEditProfileDialog(context, parentName, parentEmail),
                        child: Container(
                          padding: const EdgeInsets.all(6),
                          decoration: const BoxDecoration(
                            color: AppTheme.primary,
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(Icons.edit, size: 14, color: Colors.white),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Text(
                    parentName,
                    style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900),
                  ),
                  const SizedBox(height: 2),
                  Text(parentEmail, style: const TextStyle(fontSize: 12, color: AppTheme.grey)),
                  const SizedBox(height: 8),
                  const PillBadge(label: 'Verified Parent / Guardian', color: AppTheme.primaryLight, textColor: AppTheme.primary),
                  const SizedBox(height: 16),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                    decoration: BoxDecoration(
                      color: AppTheme.lightBg,
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceAround,
                      children: [
                        _ProfileStat(label: 'Children', value: '${children.length} Registered'),
                        Container(width: 1, height: 24, color: AppTheme.grey.withOpacity(0.3)),
                        _ProfileStat(label: 'Pending Fees', value: 'NPR ${totalPending.toInt()}'),
                      ],
                    ),
                  ),
                ],
              ),
            ).animate().fadeIn(),
            const SizedBox(height: 20),

            // Registered Children Section
            const SectionHeader(title: '👨‍👩‍👧‍👦 Registered Students'),
            const SizedBox(height: 12),
            ...children.map((c) {
              return Container(
                margin: const EdgeInsets.only(bottom: 8),
                child: AppCard(
                  padding: const EdgeInsets.all(12),
                  onTap: () => api.selectChild(c),
                  border: c.id == api.selectedChild.id ? Border.all(color: AppTheme.primary, width: 1.5) : null,
                  child: Row(
                    children: [
                      Container(
                        width: 44,
                        height: 44,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: Color(c.colorHex).withOpacity(0.15),
                        ),
                        child: Center(
                          child: Text(
                            c.avatar,
                            style: TextStyle(fontWeight: FontWeight.w900, color: Color(c.colorHex), fontSize: 18),
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(c.name, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800)),
                            Text('${c.grade} • ${c.section}', style: const TextStyle(fontSize: 11, color: AppTheme.grey)),
                          ],
                        ),
                      ),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Text('GPA ${c.gpa.toStringAsFixed(2)}', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: AppTheme.primary)),
                          Text('Rank #${c.rank}', style: const TextStyle(fontSize: 10, color: AppTheme.grey)),
                        ],
                      ),
                    ],
                  ),
                ),
              );
            }),
            const SizedBox(height: 20),

            // App Services Navigation
            const SectionHeader(title: '🔗 School Portal Services'),
            const SizedBox(height: 12),
            AppCard(
              padding: EdgeInsets.zero,
              child: Column(
                children: [
                  _SettingsTile(
                    icon: Icons.description_outlined,
                    label: 'Academic Report Card',
                    onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const ReportCardScreen())),
                  ),
                  _SettingsTile(
                    icon: Icons.people_outline_rounded,
                    label: 'Faculty & Teacher Directory',
                    onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const TeacherContactScreen())),
                  ),
                  _SettingsTile(
                    icon: Icons.event_outlined,
                    label: '1-on-1 Parent-Teacher Meetings',
                    onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const MeetingScreen())),
                  ),
                  _SettingsTile(
                    icon: Icons.celebration_outlined,
                    label: 'School Events & Activities',
                    onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const EventsScreen())),
                  ),
                  _SettingsTile(
                    icon: Icons.health_and_safety_outlined,
                    label: 'Health & Medical Vault',
                    onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const HealthScreen())),
                  ),
                  _SettingsTile(
                    icon: Icons.emoji_events_outlined,
                    label: 'Conduct & Merit Awards',
                    onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const BehaviorScreen())),
                  ),
                  _SettingsTile(
                    icon: Icons.event_busy_outlined,
                    label: 'Student Leave Applications',
                    showDivider: false,
                    onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const LeaveRequestScreen())),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // System Preferences & Security
            const SectionHeader(title: '⚙️ App Preferences & Support'),
            const SizedBox(height: 12),
            AppCard(
              padding: EdgeInsets.zero,
              child: Column(
                children: [
                  _SettingsTile(
                    icon: Icons.notifications_outlined,
                    label: 'Push Notifications',
                    trailing: Switch(
                      value: _notificationsEnabled,
                      activeColor: AppTheme.primary,
                      onChanged: (val) => setState(() => _notificationsEnabled = val),
                    ),
                    onTap: () => setState(() => _notificationsEnabled = !_notificationsEnabled),
                  ),
                  _SettingsTile(
                    icon: isDark ? Icons.dark_mode_outlined : Icons.light_mode_outlined,
                    label: 'Dark Appearance Mode',
                    trailing: Switch(
                      value: isDark,
                      activeColor: AppTheme.primary,
                      onChanged: (_) => themeProvider?.toggle(),
                    ),
                    onTap: () => themeProvider?.toggle(),
                  ),
                  _SettingsTile(
                    icon: Icons.language_outlined,
                    label: 'Language / भाषा',
                    value: _selectedLanguage,
                    onTap: () => _showLanguageDialog(context),
                  ),
                  _SettingsTile(
                    icon: Icons.security_rounded,
                    label: 'Biometric Security Lock',
                    trailing: Switch(
                      value: _biometricsEnabled,
                      activeColor: AppTheme.primary,
                      onChanged: (val) {
                        setState(() => _biometricsEnabled = val);
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(content: Text(val ? 'Biometric login enabled' : 'Biometric login disabled')),
                        );
                      },
                    ),
                    onTap: () => setState(() => _biometricsEnabled = !_biometricsEnabled),
                  ),
                  _SettingsTile(
                    icon: Icons.help_outline_rounded,
                    label: 'School Helpdesk & Helpline',
                    onTap: () => _showHelpSupportDialog(context),
                  ),
                  _SettingsTile(
                    icon: Icons.info_outline_rounded,
                    label: 'About Nepal School Hub',
                    value: 'v1.0.0 (Pro)',
                    onTap: () => _showAboutDialog(context),
                  ),
                  _SettingsTile(
                    icon: Icons.logout_rounded,
                    label: 'Sign Out Account',
                    color: AppTheme.red,
                    showDivider: false,
                    onTap: () => _showSignOutDialog(context),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showEditProfileDialog(BuildContext context, String currentName, String currentEmail) {
    final nameCtrl = TextEditingController(text: currentName);
    final emailCtrl = TextEditingController(text: currentEmail);

    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: const Text('Edit Profile Details', style: TextStyle(fontWeight: FontWeight.w900)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: nameCtrl,
              decoration: InputDecoration(
                labelText: 'Full Name',
                filled: true,
                fillColor: AppTheme.lightBg,
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: emailCtrl,
              decoration: InputDecoration(
                labelText: 'Email Address',
                filled: true,
                fillColor: AppTheme.lightBg,
              ),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Cancel')),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(context);
              await ApiService.updateProfile({'name': nameCtrl.text, 'email': emailCtrl.text});
              if (context.mounted) {
                context.read<ApiProvider>().loadAllData();
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Profile information updated successfully!')),
                );
              }
            },
            child: const Text('Save Changes'),
          ),
        ],
      ),
    );
  }

  void _showLanguageDialog(BuildContext context) {
    final languages = ['English', 'Nepali (नेपाली)', 'Hindi (हिंदी)'];
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: const Text('Select Language', style: TextStyle(fontWeight: FontWeight.w900)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: languages.map((lang) {
            return RadioListTile<String>(
              title: Text(lang, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
              value: lang.split(' ').first,
              groupValue: _selectedLanguage,
              activeColor: AppTheme.primary,
              onChanged: (val) {
                if (val != null) {
                  setState(() => _selectedLanguage = val);
                  Navigator.pop(context);
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text('App language changed to $val')),
                  );
                }
              },
            );
          }).toList(),
        ),
      ),
    );
  }

  void _showHelpSupportDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: const Text('School Helpdesk Support', style: TextStyle(fontWeight: FontWeight.w900)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: const [
            ListTile(
              leading: Icon(Icons.phone, color: AppTheme.primary),
              title: Text('Admin Hotline'),
              subtitle: Text('+977 1-4000000 / 9801234567'),
            ),
            ListTile(
              leading: Icon(Icons.email, color: AppTheme.primary),
              title: Text('Support Email'),
              subtitle: Text('support@nepalschoolhub.com'),
            ),
            ListTile(
              leading: Icon(Icons.language, color: AppTheme.primary),
              title: Text('Web Portal Desk'),
              subtitle: Text('nepalschoolhub.techprocod.com.np'),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Close')),
        ],
      ),
    );
  }

  void _showAboutDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: const Text('Nepal School Hub', style: TextStyle(fontWeight: FontWeight.w900)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: const [
            Text('ParentLink Mobile Application v1.0.0', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13)),
            SizedBox(height: 6),
            Text('Connected to complete School Management Cloud Backend.', style: TextStyle(fontSize: 12, color: AppTheme.grey)),
            SizedBox(height: 12),
            Text('Server Endpoint: https://nepalschoolhub.techprocod.com.np/api', style: TextStyle(fontSize: 10, color: AppTheme.primary, fontWeight: FontWeight.w700)),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('OK')),
        ],
      ),
    );
  }

  void _showSignOutDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: const Text('Sign Out', style: TextStyle(fontWeight: FontWeight.w900)),
        content: const Text('Are you sure you want to sign out from your parent account?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Cancel')),
          ElevatedButton(
            onPressed: () async {
              await ApiService.logout();
              if (context.mounted) {
                Navigator.pushAndRemoveUntil(
                  context,
                  MaterialPageRoute(builder: (_) => const LoginScreen()),
                  (_) => false,
                );
              }
            },
            style: ElevatedButton.styleFrom(backgroundColor: AppTheme.red, foregroundColor: Colors.white),
            child: const Text('Sign Out'),
          ),
        ],
      ),
    );
  }
}

class _ProfileStat extends StatelessWidget {
  final String label, value;
  const _ProfileStat({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Text(value, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w900, color: AppTheme.primaryDark)),
        const SizedBox(height: 2),
        Text(label, style: const TextStyle(fontSize: 10, color: AppTheme.grey, fontWeight: FontWeight.w600)),
      ],
    );
  }
}

class _SettingsTile extends StatelessWidget {
  final IconData icon;
  final String label;
  final VoidCallback onTap;
  final Color? color;
  final bool showDivider;
  final String? value;
  final Widget? trailing;

  const _SettingsTile({
    required this.icon,
    required this.label,
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
          title: Text(label, style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: c)),
          trailing: trailing ?? (value != null
              ? Text(value!, style: const TextStyle(fontSize: 11, color: AppTheme.grey, fontWeight: FontWeight.w700))
              : color == null
                  ? const Icon(Icons.chevron_right, color: AppTheme.grey, size: 18)
                  : null),
          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 2),
        ),
        if (showDivider) const Divider(height: 1, indent: 56, endIndent: 16),
      ],
    );
  }
}
