import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../widgets/common_widgets.dart';
import '../providers/teacher_provider.dart';

class ParentCommunicationScreen extends StatefulWidget {
  const ParentCommunicationScreen({super.key});
  @override
  State<ParentCommunicationScreen> createState() => _ParentCommunicationScreenState();
}

class _ParentCommunicationScreenState extends State<ParentCommunicationScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tab;

  final List<Map<String, dynamic>> _parents = [
    {
      'name': 'Gopal Sharma (Father)',
      'child': 'Aarav Sharma (Roll 1)',
      'grade': 'A+',
      'last': 'Namaste Sir, Aarav has submitted his math worksheet today.',
      'time': '10m ago',
      'unread': 0,
      'avatar': 'GS',
      'phone': '+977 9841234567',
    },
    {
      'name': 'Sunita Adhikari (Mother)',
      'child': 'Bibek Adhikari (Roll 2)',
      'grade': 'A',
      'last': 'Can we schedule a short meeting regarding second term exam preparation?',
      'time': '1h ago',
      'unread': 1,
      'avatar': 'SA',
      'phone': '+977 9851098765',
    },
    {
      'name': 'Krishna Prasad Poudel',
      'child': 'Puja Poudel (Roll 3)',
      'grade': 'A+',
      'last': 'Thank you teacher for the detailed terminal progress feedback.',
      'time': '3h ago',
      'unread': 0,
      'avatar': 'KP',
      'phone': '+977 9860112233',
    },
    {
      'name': 'Dilip Kumar Thapa',
      'child': 'Rohan Thapa (Roll 4)',
      'grade': 'B+',
      'last': 'Rohan was sick yesterday. Attached doctor prescription.',
      'time': '1d ago',
      'unread': 0,
      'avatar': 'DT',
      'phone': '+977 9849556677',
    },
  ];

  final List<Map<String, dynamic>> _meetings = [
    {
      'parent': 'Sunita Adhikari',
      'child': 'Bibek Adhikari (Grade 10 - A)',
      'date': '१० असोज २०८१ (Today)',
      'time': '03:30 PM',
      'type': 'In-Person (School)',
      'status': 'Confirmed',
    },
    {
      'parent': 'Dilip Kumar Thapa',
      'child': 'Rohan Thapa (Grade 10 - A)',
      'date': '१२ असोज २०८१ (Friday)',
      'time': '04:00 PM',
      'type': 'Phone Consultation',
      'status': 'Pending',
    },
  ];

  @override
  void initState() {
    super.initState();
    _tab = TabController(length: 3, vsync: this);
  }

  @override
  void dispose() {
    _tab.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final teacher = Provider.of<TeacherProvider>(context);

    List<Map<String, dynamic>> effectiveParents = _parents;
    if (teacher.studentList.isNotEmpty) {
      effectiveParents = teacher.studentList.map((s) {
        final parentName = s.parentName.isNotEmpty ? s.parentName : '${s.name}\'s Guardian';
        final rollStr = s.rollNumber.isNotEmpty ? 'Roll ${s.rollNumber}' : s.grade;
        final phone = s.parentPhone.isNotEmpty ? s.parentPhone : '+977 98XXXXXXXX';
        final initials = parentName.split(' ').map((w) => w.isNotEmpty ? w[0] : '').take(2).join().toUpperCase();
        return {
          'name': parentName,
          'child': '${s.name} ($rollStr)',
          'grade': s.grade,
          'last': 'Namaste Sir, how is ${s.name.split(' ').first} performing in class?',
          'time': 'Recent',
          'unread': 0,
          'avatar': initials.isNotEmpty ? initials : 'P',
          'phone': phone,
        };
      }).toList();
    }

    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
      appBar: AppBar(
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Parent Connect (अभिभावक सम्पर्क)',
              style: TextStyle(fontSize: 16.5, fontWeight: FontWeight.w900),
            ),
            Text(
              'Chat, SMS Broadcast & PTM Meeting Slots',
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
              onPressed: () => _showBroadcastSheet(isDark),
              icon: const Icon(Icons.sms_outlined, size: 16),
              label: const Text(
                'SMS All',
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primary,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ),
        ],
        bottom: TabBar(
          controller: _tab,
          labelColor: AppTheme.primary,
          unselectedLabelColor: AppTheme.darkSubtext,
          indicatorColor: AppTheme.primary,
          indicatorWeight: 3,
          indicatorSize: TabBarIndicatorSize.label,
          labelStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
          tabs: const [
            Tab(text: 'Messages (च्याट)'),
            Tab(text: 'PTM Meetings (भेटघाट)'),
            Tab(text: 'Broadcasts (सूचना)'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tab,
        children: [
          // 1. Messages Tab
          ListView.builder(
            physics: const BouncingScrollPhysics(),
            padding: const EdgeInsets.fromLTRB(18, 12, 18, 90),
            itemCount: effectiveParents.length,
            itemBuilder: (_, i) {
              final p = effectiveParents[i];
              final unread = p['unread'] as int;
              return GestureDetector(
                onTap: () => Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => ParentChatScreen(parent: p)),
                ),
                child: Container(
                  margin: const EdgeInsets.only(bottom: 10),
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: isDark ? AppTheme.darkCard : Colors.white,
                    borderRadius: BorderRadius.circular(18),
                    border: Border.all(
                      color: unread > 0
                          ? AppTheme.primary.withOpacity(0.4)
                          : (isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
                      width: unread > 0 ? 1.5 : 1,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(isDark ? 0.2 : 0.03),
                        blurRadius: 8,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Row(
                    children: [
                      Container(
                        width: 46,
                        height: 46,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          gradient: const LinearGradient(
                            colors: [Color(0xFF0F4C81), Color(0xFF2563EB)],
                          ),
                        ),
                        child: Center(
                          child: Text(
                            p['avatar'] as String,
                            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 14),
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              p['name'] as String,
                              style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800),
                            ),
                            const SizedBox(height: 2),
                            Row(
                              children: [
                                const Icon(Icons.school_rounded, size: 12, color: AppTheme.primary),
                                const SizedBox(width: 4),
                                Text(
                                  p['child'] as String,
                                  style: const TextStyle(fontSize: 11, color: AppTheme.primary, fontWeight: FontWeight.w700),
                                ),
                              ],
                            ),
                            const SizedBox(height: 3),
                            Text(
                              p['last'] as String,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: TextStyle(
                                fontSize: 12,
                                color: isDark ? Colors.white70 : AppTheme.darkSubtext,
                                fontWeight: unread > 0 ? FontWeight.w800 : FontWeight.normal,
                              ),
                            ),
                          ],
                        ),
                      ),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Text(
                            p['time'] as String,
                            style: TextStyle(
                              fontSize: 10.5,
                              color: unread > 0 ? AppTheme.primary : AppTheme.darkSubtext,
                              fontWeight: unread > 0 ? FontWeight.w800 : FontWeight.normal,
                            ),
                          ),
                          if (unread > 0) ...[
                            const SizedBox(height: 4),
                            Container(
                              width: 18,
                              height: 18,
                              decoration: const BoxDecoration(color: AppTheme.primary, shape: BoxShape.circle),
                              child: Center(
                                child: Text(
                                  '$unread',
                                  style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w900),
                                ),
                              ),
                            ),
                          ],
                        ],
                      ),
                    ],
                  ),
                ),
              ).animate().fadeIn(delay: (i * 30).ms);
            },
          ),

          // 2. Meetings Tab
          SingleChildScrollView(
            physics: const BouncingScrollPhysics(),
            padding: const EdgeInsets.fromLTRB(18, 12, 18, 90),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const SectionHeader(
                  title: 'Upcoming Parent Conferences (भेटघाट)',
                  subtitle: 'Scheduled one-on-one student discussions',
                ),
                const SizedBox(height: 10),
                ..._meetings.map((m) {
                  final isConfirmed = m['status'] == 'Confirmed';
                  return Container(
                    margin: const EdgeInsets.only(bottom: 10),
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: isDark ? AppTheme.darkCard : Colors.white,
                      borderRadius: BorderRadius.circular(18),
                      border: Border.all(
                        color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
                      ),
                    ),
                    child: Row(
                      children: [
                        Container(
                          width: 44,
                          height: 44,
                          decoration: BoxDecoration(
                            color: (isConfirmed ? AppTheme.green : AppTheme.orange).withOpacity(0.12),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Icon(
                            isConfirmed ? Icons.event_available_rounded : Icons.pending_actions_rounded,
                            color: isConfirmed ? AppTheme.green : AppTheme.orange,
                            size: 22,
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                m['parent'] as String,
                                style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800),
                              ),
                              Text(
                                m['child'] as String,
                                style: const TextStyle(fontSize: 11.5, color: AppTheme.primary, fontWeight: FontWeight.w700),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                '${m['date']} • ${m['time']}',
                                style: const TextStyle(fontSize: 11, color: AppTheme.darkSubtext),
                              ),
                            ],
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: (isConfirmed ? AppTheme.green : AppTheme.orange).withOpacity(0.12),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            (m['status'] as String).toUpperCase(),
                            style: TextStyle(
                              fontSize: 9.5,
                              fontWeight: FontWeight.w900,
                              color: isConfirmed ? AppTheme.green : AppTheme.orange,
                            ),
                          ),
                        ),
                      ],
                    ),
                  );
                }),
              ],
            ),
          ),

          // 3. Broadcasts Tab
          SingleChildScrollView(
            physics: const BouncingScrollPhysics(),
            padding: const EdgeInsets.fromLTRB(18, 12, 18, 90),
            child: Column(
              children: [
                Container(
                  padding: const EdgeInsets.all(18),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Color(0xFF0F4C81), Color(0xFF2563EB)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(22),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Instant Class SMS Gateway 📲',
                        style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.w900),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        'Send emergency absence SMS, fee reminders, or exam routine notices to all registered parents.',
                        style: TextStyle(color: Colors.white.withOpacity(0.85), fontSize: 12),
                      ),
                      const SizedBox(height: 14),
                      ElevatedButton(
                        onPressed: () => _showBroadcastSheet(isDark),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.white,
                          foregroundColor: AppTheme.primary,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        child: const Text('Compose Broadcast SMS', style: TextStyle(fontWeight: FontWeight.w800)),
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

  void _showBroadcastSheet(bool isDark) {
    final msgCtrl = TextEditingController();
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: isDark ? AppTheme.darkCard : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => Padding(
        padding: EdgeInsets.fromLTRB(
          20,
          20,
          20,
          MediaQuery.of(ctx).viewInsets.bottom + 20,
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.sms_rounded, color: AppTheme.primary, size: 22),
                SizedBox(width: 8),
                Text(
                  'Parent SMS Broadcast (अभिभावक सन्देश)',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900),
                ),
              ],
            ),
            const SizedBox(height: 14),
            TextField(
              controller: msgCtrl,
              maxLines: 4,
              decoration: InputDecoration(
                labelText: 'SMS Content',
                hintText: 'Enter message to be delivered via Nepal School Hub SMS Gateway...',
                filled: true,
                fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide.none,
                ),
              ),
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              height: 48,
              child: ElevatedButton(
                onPressed: () {
                  Navigator.pop(ctx);
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      backgroundColor: AppTheme.green,
                      content: Text('SMS broadcast successfully queued to 32 parents! ✅'),
                    ),
                  );
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.primary,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                ),
                child: const Text('Dispatch SMS Broadcast 🚀', style: TextStyle(fontWeight: FontWeight.w800)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class ParentChatScreen extends StatefulWidget {
  final Map<String, dynamic> parent;
  const ParentChatScreen({super.key, required this.parent});
  @override
  State<ParentChatScreen> createState() => _ParentChatScreenState();
}

class _ParentChatScreenState extends State<ParentChatScreen> {
  final _ctrl = TextEditingController();
  final _scroll = ScrollController();
  final List<Map<String, dynamic>> _messages = [
    {
      'text': 'Namaste Sir, I wanted to inquire about my child\'s math performance.',
      'isMe': false,
      'time': '02:00 PM',
    },
    {
      'text': 'Namaste! Aarav is attentive and scoring A+ in unit tests. Keep encouraging him on quadratic equations.',
      'isMe': true,
      'time': '02:05 PM',
    },
    {
      'text': 'Thank you Sir for the dedicated mentorship and guidance! 🙏',
      'isMe': false,
      'time': '02:10 PM',
    },
  ];

  void _send() {
    if (_ctrl.text.trim().isEmpty) return;
    setState(() {
      _messages.add({'text': _ctrl.text.trim(), 'isMe': true, 'time': 'Just now'});
      _ctrl.clear();
    });
    Future.delayed(const Duration(milliseconds: 100), () {
      _scroll.animateTo(
        _scroll.position.maxScrollExtent,
        duration: const Duration(milliseconds: 300),
        curve: Curves.easeOut,
      );
    });
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
      appBar: AppBar(
        leading: CircleIconButton(
          icon: Icons.arrow_back_ios_new_rounded,
          onTap: () => Navigator.pop(context),
          size: 38,
        ),
        leadingWidth: 60,
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              widget.parent['name'] as String,
              style: const TextStyle(fontSize: 14.5, fontWeight: FontWeight.w800),
            ),
            Text(
              'Parent of ${widget.parent['child']}',
              style: const TextStyle(fontSize: 11, color: AppTheme.darkSubtext),
            ),
          ],
        ),
      ),
      body: Column(
        children: [
          Expanded(
            child: ListView.builder(
              controller: _scroll,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              itemCount: _messages.length,
              itemBuilder: (_, i) {
                final msg = _messages[i];
                final isMe = msg['isMe'] as bool;
                return Align(
                  alignment: isMe ? Alignment.centerRight : Alignment.centerLeft,
                  child: Container(
                    margin: const EdgeInsets.only(bottom: 10),
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    constraints: BoxConstraints(maxWidth: MediaQuery.of(context).size.width * 0.75),
                    decoration: BoxDecoration(
                      gradient: isMe
                          ? const LinearGradient(
                              colors: [Color(0xFF0F4C81), Color(0xFF2563EB)],
                            )
                          : null,
                      color: isMe ? null : (isDark ? AppTheme.darkCard : Colors.white),
                      borderRadius: BorderRadius.only(
                        topLeft: const Radius.circular(18),
                        topRight: const Radius.circular(18),
                        bottomLeft: Radius.circular(isMe ? 18 : 4),
                        bottomRight: Radius.circular(isMe ? 4 : 18),
                      ),
                      border: isMe ? null : Border.all(color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
                    ),
                    child: Column(
                      crossAxisAlignment: isMe ? CrossAxisAlignment.end : CrossAxisAlignment.start,
                      children: [
                        Text(
                          msg['text'] as String,
                          style: TextStyle(
                            fontSize: 13,
                            color: isMe ? Colors.white : (isDark ? Colors.white : AppTheme.dark),
                            height: 1.35,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          msg['time'] as String,
                          style: TextStyle(
                            fontSize: 9.5,
                            color: isMe ? Colors.white70 : AppTheme.darkSubtext,
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
          ),
          Container(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
            decoration: BoxDecoration(
              color: isDark ? AppTheme.darkCard : Colors.white,
              border: Border(top: BorderSide(color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder)),
            ),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _ctrl,
                    decoration: InputDecoration(
                      hintText: 'Type a message to parent...',
                      hintStyle: const TextStyle(fontSize: 12.5, color: AppTheme.darkSubtext),
                      filled: true,
                      fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(24),
                        borderSide: BorderSide.none,
                      ),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                    ),
                    onSubmitted: (_) => _send(),
                  ),
                ),
                const SizedBox(width: 8),
                IconButton(
                  onPressed: _send,
                  icon: const Icon(Icons.send_rounded, color: AppTheme.primary),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
