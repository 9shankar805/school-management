import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
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
  Widget build(BuildContext context) {
    final api = context.watch<ApiProvider>();
    final teachers = api.teachers.where((t) {
      if (_query.isEmpty) return true;
      return t.name.toLowerCase().contains(_query.toLowerCase()) ||
          t.subject.toLowerCase().contains(_query.toLowerCase());
    }).toList();

    return Scaffold(
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
                Container(
                  width: 52,
                  height: 52,
                  decoration: BoxDecoration(
                    color: AppTheme.primaryLight,
                    shape: BoxShape.circle,
                    border: Border.all(color: AppTheme.primary.withOpacity(0.3), width: 2),
                  ),
                  child: Center(
                    child: Text(
                      teacher.avatar,
                      style: const TextStyle(fontWeight: FontWeight.w900, color: AppTheme.primary, fontSize: 20),
                    ),
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(teacher.name, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w800)),
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
                color: AppTheme.lightBg,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Row(
                children: [
                  const Icon(Icons.schedule, size: 14, color: AppTheme.grey),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Text(
                      'Hours: ${teacher.consultationHours}',
                      style: const TextStyle(fontSize: 11, color: AppTheme.dark, fontWeight: FontWeight.w600),
                    ),
                  ),
                ],
              ),
            ),
            const Divider(height: 20),
            Row(
              children: [
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
                    icon: const Icon(Icons.chat_bubble_outline, size: 16),
                    label: const Text('Send Message'),
                    style: OutlinedButton.styleFrom(
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      padding: const EdgeInsets.symmetric(vertical: 10),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: ElevatedButton.icon(
                    onPressed: () => Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const MeetingScreen()),
                    ),
                    icon: const Icon(Icons.video_call_outlined, size: 16, color: Colors.white),
                    label: const Text('Book 1-on-1'),
                    style: ElevatedButton.styleFrom(
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      padding: const EdgeInsets.symmetric(vertical: 10),
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
