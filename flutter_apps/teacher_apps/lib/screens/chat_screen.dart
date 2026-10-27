import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../widgets/common_widgets.dart';
import '../providers/teacher_provider.dart';
import '../data/app_data.dart';

class ChatListScreen extends StatelessWidget {
  const ChatListScreen({super.key});

  static const List<Map<String, dynamic>> _defaultChats = [
    {'name': 'Class Discussion Group', 'last': 'Exam schedule updated for next week', 'time': '5m', 'unread': 2, 'isGroup': true, 'avatar': 'C'},
    {'name': 'Student Support Desk', 'last': 'Thank you for the guidance teacher', 'time': '1h', 'unread': 0, 'isGroup': false, 'avatar': 'S'},
  ];

  @override
  Widget build(BuildContext context) {
    final teacher = Provider.of<TeacherProvider>(context);
    final students = teacher.studentList;
    final classes = teacher.teacherClasses;

    List<Map<String, dynamic>> chatList = [];

    for (final c in classes) {
      chatList.add({
        'name': '${c.name} Group',
        'last': '${c.subject} class discussion and notifications',
        'time': '10m',
        'unread': 0,
        'isGroup': true,
        'avatar': c.name.isNotEmpty ? c.name[0] : 'G',
      });
    }

    for (final s in students.take(6)) {
      chatList.add({
        'name': s.name,
        'last': 'Teacher, submitted the ${s.grade} assignment.',
        'time': '1h',
        'unread': 0,
        'isGroup': false,
        'avatar': s.name.isNotEmpty ? s.name[0] : 'S',
      });
    }

    if (chatList.isEmpty) {
      chatList = _defaultChats;
    }

    final avatarColors = [
      AppTheme.primary, AppTheme.blue, AppTheme.green,
      AppTheme.orange, AppTheme.purple, AppTheme.teal
    ];

    return Scaffold(
      appBar: AppBar(
        title: const Text('Messages'),
        automaticallyImplyLeading: false,
        actions: [
          CircleIconButton(icon: Icons.search_rounded, onTap: () {}, size: 38),
          const SizedBox(width: 8),
          CircleIconButton(icon: Icons.edit_outlined, onTap: () {}, size: 38),
          const SizedBox(width: 16),
        ],
      ),
      body: Column(children: [
        // Online students
        if (students.isNotEmpty)
          SizedBox(
            height: 90,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.fromLTRB(20, 8, 20, 8),
              itemCount: students.take(6).length,
              itemBuilder: (_, i) {
                final s = students[i];
                return _OnlineAvatar(
                  name: s.name.split(' ').first,
                  letter: s.name.isNotEmpty ? s.name[0] : 'S',
                  color: avatarColors[i % avatarColors.length],
                );
              },
            ),
          )
        else
          SizedBox(
            height: 90,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.fromLTRB(20, 8, 20, 8),
              itemCount: 4,
              itemBuilder: (_, i) => _OnlineAvatar(
                name: ['Student 1', 'Student 2', 'Student 3', 'Student 4'][i],
                letter: ['1', '2', '3', '4'][i],
                color: avatarColors[i % avatarColors.length],
              ),
            ),
          ),
        const Divider(height: 1),
        Expanded(
          child: ListView.builder(
            padding: const EdgeInsets.symmetric(vertical: 8),
            itemCount: chatList.length,
            itemBuilder: (_, i) => _ChatTile(chat: chatList[i])
                .animate().fadeIn(delay: (i * 60).ms),
          ),
        ),
      ]),
    );
  }
}

class _OnlineAvatar extends StatelessWidget {
  final String name, letter;
  final Color color;
  const _OnlineAvatar({required this.name, required this.letter, required this.color});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(right: 16),
      child: Column(children: [
        Stack(children: [
          Container(
            width: 48, height: 48,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              gradient: LinearGradient(colors: [color, color.withOpacity(0.6)]),
            ),
            child: Center(child: Text(letter,
                style: const TextStyle(color: Colors.white,
                    fontWeight: FontWeight.w800, fontSize: 18))),
          ),
          Positioned(right: 0, bottom: 0,
            child: Container(
              width: 12, height: 12,
              decoration: BoxDecoration(
                  color: AppTheme.green, shape: BoxShape.circle,
                  border: Border.all(color: Colors.white, width: 2)),
            )),
        ]),
        const SizedBox(height: 4),
        Text(name, style: const TextStyle(fontSize: 10, color: AppTheme.grey)),
      ]),
    );
  }
}

class _ChatTile extends StatelessWidget {
  final Map<String, dynamic> chat;
  const _ChatTile({required this.chat});

  @override
  Widget build(BuildContext context) {
    final unread = chat['unread'] as int;
    final isGroup = chat['isGroup'] as bool;
    return ListTile(
      onTap: () => Navigator.push(context,
          MaterialPageRoute(builder: (_) =>
              ChatDetailScreen(name: chat['name'] as String))),
      contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 4),
      leading: Stack(children: [
        Container(
          width: 50, height: 50,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            gradient: LinearGradient(
              colors: isGroup
                  ? [AppTheme.primary, AppTheme.secondary]
                  : [AppTheme.blue, AppTheme.teal],
            ),
          ),
          child: Center(child: Text(chat['avatar'] as String,
              style: const TextStyle(color: Colors.white,
                  fontWeight: FontWeight.w800, fontSize: 18))),
        ),
        if (!isGroup)
          Positioned(right: 0, bottom: 0,
            child: Container(
              width: 12, height: 12,
              decoration: BoxDecoration(
                  color: AppTheme.green, shape: BoxShape.circle,
                  border: Border.all(color: Colors.white, width: 2)),
            )),
      ]),
      title: Text(chat['name'] as String,
          style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700,
              color: unread > 0 ? null : AppTheme.grey)),
      subtitle: Text(chat['last'] as String,
          maxLines: 1, overflow: TextOverflow.ellipsis,
          style: TextStyle(fontSize: 12,
              color: unread > 0 ? null : AppTheme.grey,
              fontWeight: unread > 0 ? FontWeight.w600 : FontWeight.normal)),
      trailing: Column(mainAxisAlignment: MainAxisAlignment.center,
          crossAxisAlignment: CrossAxisAlignment.end, children: [
        Text(chat['time'] as String,
            style: TextStyle(fontSize: 11,
                color: unread > 0 ? AppTheme.primary : AppTheme.grey)),
        const SizedBox(height: 4),
        if (unread > 0)
          Container(
            width: 20, height: 20,
            decoration: const BoxDecoration(
                color: AppTheme.primary, shape: BoxShape.circle),
            child: Center(child: Text('$unread',
                style: const TextStyle(color: Colors.white,
                    fontSize: 10, fontWeight: FontWeight.w700))),
          ),
      ]),
    );
  }
}

class ChatDetailScreen extends StatefulWidget {
  final String name;
  const ChatDetailScreen({super.key, required this.name});
  @override
  State<ChatDetailScreen> createState() => _ChatDetailScreenState();
}

class _ChatDetailScreenState extends State<ChatDetailScreen> {
  final _ctrl = TextEditingController();
  final _scroll = ScrollController();
  final List<Map<String, dynamic>> _messages = [
    {'text': 'Good morning! I had a question about the exam.', 'isMe': false, 'time': '9:00 AM'},
    {'text': 'Good morning! Sure, what would you like to know?', 'isMe': true, 'time': '9:02 AM'},
    {'text': 'Will the integration chapter be included?', 'isMe': false, 'time': '9:03 AM'},
    {'text': 'Yes, chapters 5 through 8 are included. Focus on integration techniques.', 'isMe': true, 'time': '9:05 AM'},
    {'text': 'Thank you so much! I\'ll prepare accordingly.', 'isMe': false, 'time': '9:06 AM'},
    {'text': 'Great! Let me know if you have more questions.', 'isMe': true, 'time': '9:07 AM'},
  ];

  void _send() {
    if (_ctrl.text.trim().isEmpty) return;
    setState(() {
      _messages.add({'text': _ctrl.text.trim(), 'isMe': true, 'time': 'Now'});
      _ctrl.clear();
    });
    Future.delayed(const Duration(milliseconds: 100), () {
      _scroll.animateTo(_scroll.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300), curve: Curves.easeOut);
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        leading: CircleIconButton(icon: Icons.arrow_back_ios_new,
            onTap: () => Navigator.pop(context), size: 38),
        leadingWidth: 60,
        title: Row(children: [
          Container(
            width: 36, height: 36,
            decoration: const BoxDecoration(
              shape: BoxShape.circle,
              gradient: LinearGradient(
                  colors: [AppTheme.primary, AppTheme.secondary]),
            ),
            child: Center(child: Text(widget.name[0],
                style: const TextStyle(color: Colors.white,
                    fontWeight: FontWeight.w800))),
          ),
          const SizedBox(width: 10),
          Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(widget.name,
                style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700)),
            const Text('Online',
                style: TextStyle(fontSize: 11, color: AppTheme.green)),
          ]),
        ]),
        actions: [
          IconButton(icon: const Icon(Icons.videocam_outlined), onPressed: () {}),
          IconButton(icon: const Icon(Icons.call_outlined), onPressed: () {}),
        ],
      ),
      body: Column(children: [
        Expanded(
          child: ListView.builder(
            controller: _scroll,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            itemCount: _messages.length,
            itemBuilder: (_, i) => _Bubble(msg: _messages[i])
                .animate().fadeIn(delay: (i * 30).ms),
          ),
        ),
        _InputBar(controller: _ctrl, onSend: _send),
      ]),
    );
  }
}

class _Bubble extends StatelessWidget {
  final Map<String, dynamic> msg;
  const _Bubble({required this.msg});

  @override
  Widget build(BuildContext context) {
    final isMe = msg['isMe'] as bool;
    return Align(
      alignment: isMe ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        margin: const EdgeInsets.only(bottom: 8),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
        constraints: BoxConstraints(
            maxWidth: MediaQuery.of(context).size.width * 0.72),
        decoration: BoxDecoration(
          gradient: isMe ? const LinearGradient(
              colors: [AppTheme.primary, AppTheme.secondary]) : null,
          color: isMe ? null
              : (Theme.of(context).brightness == Brightness.dark
                  ? AppTheme.darkCard : const Color(0xFFF0F0F0)),
          borderRadius: BorderRadius.only(
            topLeft: const Radius.circular(18),
            topRight: const Radius.circular(18),
            bottomLeft: Radius.circular(isMe ? 18 : 4),
            bottomRight: Radius.circular(isMe ? 4 : 18),
          ),
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.end, children: [
          Text(msg['text'] as String,
              style: TextStyle(fontSize: 14,
                  color: isMe ? Colors.white : null, height: 1.4)),
          const SizedBox(height: 4),
          Text(msg['time'] as String,
              style: TextStyle(fontSize: 10,
                  color: isMe ? Colors.white60 : AppTheme.grey)),
        ]),
      ),
    );
  }
}

class _InputBar extends StatelessWidget {
  final TextEditingController controller;
  final VoidCallback onSend;
  const _InputBar({required this.controller, required this.onSend});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Container(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkCard : Colors.white,
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.06),
            blurRadius: 10, offset: const Offset(0, -2))],
      ),
      child: Row(children: [
        IconButton(
            icon: const Icon(Icons.attach_file_rounded, color: AppTheme.grey),
            onPressed: () {}),
        Expanded(
          child: TextField(
            controller: controller,
            decoration: InputDecoration(
              hintText: 'Type a message...',
              hintStyle: const TextStyle(color: AppTheme.grey),
              filled: true,
              fillColor: isDark ? AppTheme.darkSurface : AppTheme.lightBg,
              border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(24),
                  borderSide: BorderSide.none),
              contentPadding:
                  const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
            ),
            onSubmitted: (_) => onSend(),
          ),
        ),
        const SizedBox(width: 8),
        GestureDetector(
          onTap: onSend,
          child: Container(
            width: 44, height: 44,
            decoration: const BoxDecoration(
              shape: BoxShape.circle,
              gradient: LinearGradient(
                  colors: [AppTheme.primary, AppTheme.secondary]),
            ),
            child: const Icon(Icons.send_rounded, color: Colors.white, size: 20),
          ),
        ),
      ]),
    );
  }
}
