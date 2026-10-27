import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import '../theme/app_theme.dart';
import '../widgets/common_widgets.dart';

class TodoScreen extends StatefulWidget {
  const TodoScreen({super.key});
  @override
  State<TodoScreen> createState() => _TodoScreenState();
}

class _TodoScreenState extends State<TodoScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tab;

  final List<Map<String, dynamic>> _todos = [
    {'title': 'Grade Calculus Problem Set 5', 'category': 'Grading',
      'due': 'Today', 'priority': 'high', 'done': false,
      'colorHex': 0xFFE3F2FD, 'icon': Icons.grade_outlined},
    {'title': 'Prepare Mid-Term Exam Paper', 'category': 'Exam',
      'due': 'Aug 20', 'priority': 'high', 'done': false,
      'colorHex': 0xFFFCE4EC, 'icon': Icons.quiz_outlined},
    {'title': 'Update Gradebook for Grade 12-A', 'category': 'Admin',
      'due': 'Aug 18', 'priority': 'medium', 'done': false,
      'colorHex': 0xFFE8F5E9, 'icon': Icons.bar_chart_rounded},
    {'title': 'Reply to pending parent message', 'category': 'Communication',
      'due': 'Today', 'priority': 'high', 'done': false,
      'colorHex': 0xFFF3E5F5, 'icon': Icons.chat_bubble_outline_rounded},
    {'title': 'Upload lecture slides for Grade 11-A', 'category': 'Resources',
      'due': 'Aug 19', 'priority': 'medium', 'done': false,
      'colorHex': 0xFFFFF3E0, 'icon': Icons.upload_outlined},
    {'title': 'Submit attendance report', 'category': 'Admin',
      'due': 'Aug 17', 'priority': 'low', 'done': true,
      'colorHex': 0xFFE8EAF6, 'icon': Icons.how_to_reg_outlined},
    {'title': 'Create quiz for Grade 10-A', 'category': 'Quiz',
      'due': 'Aug 22', 'priority': 'medium', 'done': true,
      'colorHex': 0xFFE3F2FD, 'icon': Icons.help_outline_rounded},
  ];

  @override
  void initState() {
    super.initState();
    _tab = TabController(length: 3, vsync: this);
  }

  @override
  void dispose() { _tab.dispose(); super.dispose(); }

  List<Map<String, dynamic>> get _pending =>
      _todos.where((t) => !(t['done'] as bool)).toList();
  List<Map<String, dynamic>> get _completed =>
      _todos.where((t) => t['done'] as bool).toList();

  Color _priorityColor(String p) {
    if (p == 'high') return AppTheme.red;
    if (p == 'medium') return AppTheme.orange;
    return AppTheme.green;
  }

  void _toggle(Map<String, dynamic> todo) {
    setState(() => todo['done'] = !(todo['done'] as bool));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('To-Do List'),
        leading: CircleIconButton(icon: Icons.arrow_back_ios_new,
            onTap: () => Navigator.pop(context), size: 38),
        leadingWidth: 60,
        actions: [
          CircleIconButton(
            icon: Icons.add,
            onTap: () => _showAddSheet(),
            bgColor: AppTheme.primary,
            iconColor: Colors.white,
            size: 38,
          ),
          const SizedBox(width: 16),
        ],
        bottom: TabBar(
          controller: _tab,
          labelColor: AppTheme.primary,
          unselectedLabelColor: AppTheme.grey,
          indicatorColor: AppTheme.primary,
          indicatorSize: TabBarIndicatorSize.label,
          tabs: [
            Tab(text: 'All (${_todos.length})'),
            Tab(text: 'Pending (${_pending.length})'),
            Tab(text: 'Done (${_completed.length})'),
          ],
        ),
      ),
      body: Column(children: [
        // Progress bar
        Padding(
          padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
          child: AppCard(
            padding: const EdgeInsets.all(14),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                const Text('Today\'s Progress',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
                Text('${_completed.length}/${_todos.length} done',
                    style: const TextStyle(fontSize: 12,
                        fontWeight: FontWeight.w700, color: AppTheme.primary)),
              ]),
              const SizedBox(height: 8),
              ClipRRect(
                borderRadius: BorderRadius.circular(6),
                child: LinearProgressIndicator(
                  value: _todos.isEmpty ? 0 : _completed.length / _todos.length,
                  backgroundColor: AppTheme.primary.withOpacity(0.1),
                  valueColor: const AlwaysStoppedAnimation<Color>(AppTheme.primary),
                  minHeight: 10,
                ),
              ),
            ]),
          ),
        ),
        const SizedBox(height: 8),
        Expanded(
          child: TabBarView(
            controller: _tab,
            children: [
              _TodoList(todos: _todos, onToggle: _toggle,
                  priorityColor: _priorityColor),
              _TodoList(todos: _pending, onToggle: _toggle,
                  priorityColor: _priorityColor),
              _TodoList(todos: _completed, onToggle: _toggle,
                  priorityColor: _priorityColor),
            ],
          ),
        ),
      ]),
    );
  }

  void _showAddSheet() {
    final titleCtrl = TextEditingController();
    String priority = 'medium';
    String category = 'Admin';
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (_) => StatefulBuilder(
        builder: (ctx, setModal) => Padding(
          padding: EdgeInsets.fromLTRB(24, 24, 24,
              MediaQuery.of(ctx).viewInsets.bottom + 24),
          child: Column(mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start, children: [
            const Text('New Task', style: TextStyle(
                fontSize: 18, fontWeight: FontWeight.w800, color: AppTheme.dark)),
            const SizedBox(height: 20),
            TextField(controller: titleCtrl,
                decoration: InputDecoration(labelText: 'Task Title',
                    filled: true, fillColor: AppTheme.lightBg,
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12),
                        borderSide: BorderSide.none))),
            const SizedBox(height: 12),
            DropdownButtonFormField<String>(
              value: category,
              decoration: InputDecoration(labelText: 'Category',
                  filled: true, fillColor: AppTheme.lightBg,
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12),
                      borderSide: BorderSide.none)),
              items: ['Grading', 'Exam', 'Admin', 'Communication', 'Resources', 'Quiz']
                  .map((c) => DropdownMenuItem(value: c, child: Text(c))).toList(),
              onChanged: (v) => setModal(() => category = v!),
            ),
            const SizedBox(height: 12),
            const Text('Priority', style: TextStyle(fontWeight: FontWeight.w600)),
            const SizedBox(height: 8),
            Row(children: ['high', 'medium', 'low'].map((p) {
              final sel = priority == p;
              final color = _priorityColor(p);
              return GestureDetector(
                onTap: () => setModal(() => priority = p),
                child: Container(
                  margin: const EdgeInsets.only(right: 8),
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  decoration: BoxDecoration(
                    color: sel ? color : AppTheme.lightBg,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(p.toUpperCase(),
                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700,
                          color: sel ? Colors.white : AppTheme.grey)),
                ),
              );
            }).toList()),
            const SizedBox(height: 20),
            SizedBox(width: double.infinity, height: 50,
              child: ElevatedButton(
                onPressed: () {
                  if (titleCtrl.text.isNotEmpty) {
                    setState(() {
                      _todos.insert(0, {
                        'title': titleCtrl.text,
                        'category': category,
                        'due': 'Today',
                        'priority': priority,
                        'done': false,
                        'colorHex': 0xFFE3F2FD,
                        'icon': Icons.task_outlined,
                      });
                    });
                    Navigator.pop(ctx);
                  }
                },
                style: ElevatedButton.styleFrom(backgroundColor: AppTheme.primary,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14))),
                child: const Text('Add Task', style: TextStyle(fontWeight: FontWeight.w700)),
              ),
            ),
          ]),
        ),
      ),
    );
  }
}

class _TodoList extends StatelessWidget {
  final List<Map<String, dynamic>> todos;
  final Function(Map<String, dynamic>) onToggle;
  final Color Function(String) priorityColor;
  const _TodoList({required this.todos, required this.onToggle,
    required this.priorityColor});

  @override
  Widget build(BuildContext context) {
    if (todos.isEmpty) {
      return const Center(child: Column(
          mainAxisAlignment: MainAxisAlignment.center, children: [
        Icon(Icons.task_alt_rounded, size: 64, color: AppTheme.grey),
        SizedBox(height: 12),
        Text('All caught up! 🎉', style: TextStyle(fontSize: 16, color: AppTheme.grey)),
      ]));
    }
    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(20, 12, 20, 100),
      itemCount: todos.length,
      itemBuilder: (_, i) {
        final t = todos[i];
        final isDone = t['done'] as bool;
        final pColor = priorityColor(t['priority'] as String);
        return Dismissible(
          key: Key(t['title'] as String),
          direction: DismissDirection.endToStart,
          background: Container(
            alignment: Alignment.centerRight,
            padding: const EdgeInsets.only(right: 20),
            margin: const EdgeInsets.only(bottom: 10),
            decoration: BoxDecoration(
                color: AppTheme.red.withOpacity(0.1),
                borderRadius: BorderRadius.circular(16)),
            child: const Icon(Icons.delete_outline, color: AppTheme.red),
          ),
          onDismissed: (_) {},
          child: Container(
            margin: const EdgeInsets.only(bottom: 10),
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: isDone ? AppTheme.lightBg : Colors.white,
              borderRadius: BorderRadius.circular(16),
              boxShadow: isDone ? null : [BoxShadow(
                  color: Colors.black.withOpacity(0.04),
                  blurRadius: 8, offset: const Offset(0, 2))],
            ),
            child: Row(children: [
              GestureDetector(
                onTap: () => onToggle(t),
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  width: 24, height: 24,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: isDone ? AppTheme.green : Colors.transparent,
                    border: Border.all(
                        color: isDone ? AppTheme.green : AppTheme.grey.withOpacity(0.4),
                        width: 2),
                  ),
                  child: isDone
                      ? const Icon(Icons.check, size: 14, color: Colors.white)
                      : null,
                ),
              ),
              const SizedBox(width: 12),
              Container(
                width: 36, height: 36,
                decoration: BoxDecoration(
                    color: Color(t['colorHex'] as int),
                    borderRadius: BorderRadius.circular(10)),
                child: Icon(t['icon'] as IconData,
                    size: 18, color: AppTheme.dark.withOpacity(0.7)),
              ),
              const SizedBox(width: 10),
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(t['title'] as String,
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700,
                        decoration: isDone ? TextDecoration.lineThrough : null,
                        color: isDone ? AppTheme.grey : null)),
                Row(children: [
                  Text(t['category'] as String,
                      style: const TextStyle(fontSize: 10, color: AppTheme.grey)),
                  const SizedBox(width: 6),
                  const Icon(Icons.calendar_today_outlined, size: 10, color: AppTheme.grey),
                  const SizedBox(width: 2),
                  Text(t['due'] as String,
                      style: TextStyle(fontSize: 10,
                          color: t['due'] == 'Today' && !isDone
                              ? AppTheme.red : AppTheme.grey,
                          fontWeight: t['due'] == 'Today' && !isDone
                              ? FontWeight.w700 : FontWeight.normal)),
                ]),
              ])),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                    color: pColor.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8)),
                child: Text((t['priority'] as String).toUpperCase(),
                    style: TextStyle(fontSize: 9,
                        fontWeight: FontWeight.w800, color: pColor)),
              ),
            ]),
          ),
        ).animate().fadeIn(delay: (i * 50).ms);
      },
    );
  }
}
