import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../providers/api_provider.dart';
import '../widgets/common_widgets.dart';
import 'chat_screen.dart';
import 'meeting_screen.dart';

class TeacherContactScreen extends StatefulWidget {
  const TeacherContactScreen({super.key});
  @override
  State<TeacherContactScreen> createState() => _TeacherContactScreenState();
}

class _TeacherContactScreenState extends State<TeacherContactScreen> {
  final TextEditingController _searchCtrl = TextEditingController();
  String _query = '';

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final api = context.watch<ApiProvider>();
    final teachers = api.teachers.where((t) {
      if (_query.isEmpty) return true;
      return t.name.toLowerCase().contains(_query.toLowerCase()) ||
          t.subject.toLowerCase().contains(_query.toLowerCase()) ||
          t.designation.toLowerCase().contains(_query.toLowerCase());
    }).toList();

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Faculty & Teachers'),
        leading: CircleIconButton(
          icon: Icons.arrow_back_ios_new,
          onTap: () => Navigator.pop(context),
          size: 38,
        ),
        leadingWidth: 60,
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 8, 20, 12),
            child: TextField(
              controller: _searchCtrl,
              onChanged: (val) => setState(() => _query = val),
              decoration: InputDecoration(
                hintText: 'Search educator by name or subject...',
                prefixIcon: const Icon(Icons.search, color: AppTheme.grey),
                suffixIcon: _query.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear, size: 18),
                        onPressed: () {
                          _searchCtrl.clear();
                          setState(() => _query = '');
                        },
                      )
                    : null,
              ),
            ),
          ),
          Expanded(
            child: teachers.isEmpty
                ? const EmptyStateWidget(
                    icon: Icons.people_outline,
                    title: 'No teachers found',
                    subtitle: 'Try searching for a different subject or teacher name.',
                  )
                : ListView.builder(
                    padding: const EdgeInsets.fromLTRB(20, 4, 20, 40),
                    itemCount: teachers.length,
                    itemBuilder: (context, idx) {
                      final t = teachers[idx];
                      return _TeacherCard(teacher: t).animate().fadeIn(delay: (idx * 50).ms);
                    },
                  ),
          ),
        ],
      ),
    );
  }
}

class _TeacherCard extends StatelessWidget {
  final TeacherContact teacher;
  const _TeacherCard({required this.teacher});

  Future<void> _callTeacher(BuildContext context, String phone) async {
    final uri = Uri(scheme: 'tel', path: phone.replaceAll(RegExp(r'\s+'), ''));
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    } else if (context.mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Dialing $phone...')),
      );
    }
  }

  Future<void> _emailTeacher(BuildContext context, String email) async {
    final uri = Uri(scheme: 'mailto', path: email);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    } else if (context.mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Opening email app for $email...')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      child: AppCard(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Stack(
                  children: [
                    Container(
                      width: 52,
                      height: 52,
                      decoration: BoxDecoration(
                        color: AppTheme.primaryLight,
                        shape: BoxShape.circle,
                        border: Border.all(color: AppTheme.primary.withValues(alpha: 0.3), width: 2),
                      ),
                      child: Center(
                        child: Text(
                          teacher.avatar,
                          style: const TextStyle(fontWeight: FontWeight.w900, color: AppTheme.primary, fontSize: 18),
                        ),
                      ),
                    ),
                    Positioned(
                      bottom: 0,
                      right: 0,
                      child: Container(
                        width: 14,
                        height: 14,
                        decoration: BoxDecoration(
                          color: teacher.isAvailable ? const Color(0xFF10B981) : Colors.grey,
                          shape: BoxShape.circle,
                          border: Border.all(color: Colors.white, width: 2),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Text(
                              teacher.name,
                              style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w800),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                          Row(
                            children: [
                              const Icon(Icons.star_rounded, color: AppTheme.amber, size: 16),
                              const SizedBox(width: 2),
                              Text('${teacher.rating}', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800)),
                            ],
                          ),
                        ],
                      ),
                      const SizedBox(height: 2),
                      Text(
                        '${teacher.subject} • ${teacher.designation}',
                        style: const TextStyle(fontSize: 11, color: AppTheme.primary, fontWeight: FontWeight.w700),
                      ),
                      Text(
                        'Office: ${teacher.roomNo}',
                        style: const TextStyle(fontSize: 11, color: AppTheme.grey),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: const Color(0xFFF1F5F9),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Row(
                children: [
                  const Icon(Icons.schedule, size: 14, color: AppTheme.grey),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Text(
                      'Consultation: ${teacher.consultationHours}',
                      style: const TextStyle(fontSize: 10.5, color: Color(0xFF0F172A), fontWeight: FontWeight.w600),
                    ),
                  ),
                ],
              ),
            ),
            const Divider(height: 20),
            Row(
              children: [
                IconButton(
                  icon: const Icon(Icons.phone_outlined, color: Color(0xFF047857), size: 20),
                  tooltip: 'Call Faculty',
                  onPressed: () => _callTeacher(context, teacher.phone),
                ),
                IconButton(
                  icon: const Icon(Icons.email_outlined, color: AppTheme.primary, size: 20),
                  tooltip: 'Send Email',
                  onPressed: () => _emailTeacher(context, teacher.email),
                ),
                const SizedBox(width: 4),
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () {
                      final conv = ConversationItem(
                        id: teacher.id,
                        teacherId: teacher.id,
                        teacherName: teacher.name,
                        teacherAvatar: teacher.avatar,
                        subject: teacher.subject,
                        lastMessage: 'Hello ${teacher.name}, I have an inquiry.',
                        lastMessageTime: 'Now',
                      );
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => ChatConversationScreen(conversation: conv)),
                      );
                    },
                    icon: const Icon(Icons.chat_bubble_outline, size: 14),
                    label: const Text('Chat', style: TextStyle(fontWeight: FontWeight.w700)),
                    style: OutlinedButton.styleFrom(
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      padding: const EdgeInsets.symmetric(vertical: 8),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: ElevatedButton.icon(
                    onPressed: () => Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const MeetingScreen()),
                    ),
                    icon: const Icon(Icons.video_call_outlined, size: 16, color: Colors.white),
                    label: const Text('1-on-1', style: TextStyle(fontWeight: FontWeight.w700)),
                    style: ElevatedButton.styleFrom(
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      padding: const EdgeInsets.symmetric(vertical: 8),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
