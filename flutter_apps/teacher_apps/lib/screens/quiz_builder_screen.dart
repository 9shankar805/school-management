import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../widgets/common_widgets.dart';
import '../providers/teacher_provider.dart';

class QuizBuilderScreen extends StatefulWidget {
  const QuizBuilderScreen({super.key});
  @override
  State<QuizBuilderScreen> createState() => _QuizBuilderScreenState();
}

class _QuizBuilderScreenState extends State<QuizBuilderScreen> {
  final List<Map<String, dynamic>> _quizzes = [
    {
      'title': 'Quadratic Equations Quiz',
      'class': 'Grade 10 - A',
      'questions': 10,
      'duration': '20 min',
      'status': 'active',
      'colorHex': 0xFFE3F2FD,
      'responses': 24,
      'total': 32,
    },
    {
      'title': 'Integration Basics',
      'class': 'Grade 11 - A',
      'questions': 8,
      'duration': '15 min',
      'status': 'draft',
      'colorHex': 0xFFF3E5F5,
      'responses': 0,
      'total': 28,
    },
    {
      'title': 'Statistics Mid-Check',
      'class': 'Grade 12 - A',
      'questions': 15,
      'duration': '30 min',
      'status': 'closed',
      'colorHex': 0xFFE8F5E9,
      'responses': 25,
      'total': 25,
    },
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Quiz Builder'),
        leading: CircleIconButton(icon: Icons.arrow_back_ios_new,
            onTap: () => Navigator.pop(context), size: 38),
        leadingWidth: 60,
        actions: [
          CircleIconButton(
            icon: Icons.add,
            onTap: () => _showCreateQuizSheet(),
            bgColor: AppTheme.primary,
            iconColor: Colors.white,
            size: 38,
          ),
          const SizedBox(width: 16),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 24),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          // Stats
          Row(children: [
            _QuizStat(label: 'Total Quizzes', value: '${_quizzes.length}', color: AppTheme.primary),
            const SizedBox(width: 12),
            _QuizStat(label: 'Active', value: '${_quizzes.where((q) => q['status'] == 'active').length}', color: AppTheme.green),
            const SizedBox(width: 12),
            _QuizStat(label: 'Drafts', value: '${_quizzes.where((q) => q['status'] == 'draft').length}', color: AppTheme.orange),
          ]),
          const SizedBox(height: 24),
          const SectionHeader(title: 'My Quizzes'),
          const SizedBox(height: 12),
          ..._quizzes.asMap().entries.map((e) =>
              _QuizCard(quiz: e.value,
                  onTap: () => Navigator.push(context,
                      MaterialPageRoute(builder: (_) => QuizDetailScreen(quiz: e.value))))
                  .animate().fadeIn(delay: (e.key * 80).ms).slideY(begin: 0.1)),
          const SizedBox(height: 24),
          const SectionHeader(title: 'Quick Templates'),
          const SizedBox(height: 12),
          ..._templates.map((t) => AppCard(
            padding: const EdgeInsets.all(14),
            onTap: () => _showCreateQuizSheet(),
            child: Row(children: [
              Container(
                width: 44, height: 44,
                decoration: BoxDecoration(
                    color: (t['color'] as Color).withOpacity(0.12),
                    borderRadius: BorderRadius.circular(12)),
                child: Icon(t['icon'] as IconData,
                    color: t['color'] as Color, size: 22),
              ),
              const SizedBox(width: 12),
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(t['title'] as String,
                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
                Text(t['desc'] as String,
                    style: const TextStyle(fontSize: 11, color: AppTheme.grey)),
              ])),
              const Icon(Icons.arrow_forward_ios, size: 14, color: AppTheme.grey),
            ]),
          )),
        ]),
      ),
    );
  }

  static const List<Map<String, dynamic>> _templates = [
    {'title': 'Multiple Choice', 'desc': '10 MCQ questions, auto-graded',
      'icon': Icons.radio_button_checked_rounded, 'color': AppTheme.blue},
    {'title': 'True / False', 'desc': 'Quick 5-question T/F check',
      'icon': Icons.check_circle_outline_rounded, 'color': AppTheme.green},
    {'title': 'Short Answer', 'desc': 'Open-ended response questions',
      'icon': Icons.edit_note_rounded, 'color': AppTheme.purple},
    {'title': 'Exit Ticket', 'desc': '3 questions, end-of-class check',
      'icon': Icons.exit_to_app_rounded, 'color': AppTheme.orange},
  ];

  void _showCreateQuizSheet() {
    final titleCtrl = TextEditingController();
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (_) => Padding(
        padding: EdgeInsets.fromLTRB(24, 24, 24,
            MediaQuery.of(context).viewInsets.bottom + 24),
        child: Column(mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start, children: [
          const Text('Create Quiz', style: TextStyle(
              fontSize: 18, fontWeight: FontWeight.w800, color: AppTheme.dark)),
          const SizedBox(height: 20),
          TextField(controller: titleCtrl,
              decoration: InputDecoration(labelText: 'Quiz Title',
                  filled: true, fillColor: AppTheme.lightBg,
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12),
                      borderSide: BorderSide.none))),
          const SizedBox(height: 12),
          DropdownButtonFormField<String>(
            value: Provider.of<TeacherProvider>(context, listen: false).teacherClasses.first.section,
            decoration: InputDecoration(labelText: 'Assign to Class',
                filled: true, fillColor: AppTheme.lightBg,
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12),
                    borderSide: BorderSide.none)),
            items: Provider.of<TeacherProvider>(context, listen: false).teacherClasses.map((c) => DropdownMenuItem(
                value: c.section, child: Text(c.section))).toList(),
            onChanged: (_) {},
          ),
          const SizedBox(height: 20),
          SizedBox(width: double.infinity, height: 50,
            child: ElevatedButton(
              onPressed: () => Navigator.pop(context),
              style: ElevatedButton.styleFrom(backgroundColor: AppTheme.primary,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14))),
              child: const Text('Create & Add Questions',
                  style: TextStyle(fontWeight: FontWeight.w700)),
            ),
          ),
        ]),
      ),
    );
  }
}

class _QuizStat extends StatelessWidget {
  final String label, value;
  final Color color;
  const _QuizStat({required this.label, required this.value, required this.color});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 14),
        decoration: BoxDecoration(
          color: isDark ? AppTheme.darkCard : Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05),
              blurRadius: 8, offset: const Offset(0, 2))],
        ),
        child: Column(children: [
          Text(value, style: TextStyle(fontSize: 22,
              fontWeight: FontWeight.w900, color: color)),
          Text(label, style: const TextStyle(fontSize: 10, color: AppTheme.grey)),
        ]),
      ),
    );
  }
}

class _QuizCard extends StatelessWidget {
  final Map<String, dynamic> quiz;
  final VoidCallback onTap;
  const _QuizCard({required this.quiz, required this.onTap});

  Color get _statusColor {
    switch (quiz['status']) {
      case 'active': return AppTheme.green;
      case 'draft': return AppTheme.orange;
      default: return AppTheme.grey;
    }
  }

  @override
  Widget build(BuildContext context) {
    final responses = quiz['responses'] as int;
    final total = quiz['total'] as int;
    return GestureDetector(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.only(bottom: 12),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05),
              blurRadius: 8, offset: const Offset(0, 2))],
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            Container(
              width: 48, height: 48,
              decoration: BoxDecoration(
                  color: Color(quiz['colorHex'] as int),
                  borderRadius: BorderRadius.circular(14)),
              child: const Icon(Icons.quiz_rounded, color: AppTheme.dark, size: 24),
            ),
            const SizedBox(width: 12),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(quiz['title'] as String,
                  style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700)),
              Text(quiz['class'] as String,
                  style: const TextStyle(fontSize: 12, color: AppTheme.grey)),
            ])),
            StatusBadge(label: (quiz['status'] as String).toUpperCase(),
                color: _statusColor),
          ]),
          const SizedBox(height: 12),
          Row(children: [
            _QuizMeta(icon: Icons.help_outline, label: '${quiz['questions']} questions'),
            const SizedBox(width: 12),
            _QuizMeta(icon: Icons.timer_outlined, label: quiz['duration'] as String),
            const Spacer(),
            Text('$responses/$total responded',
                style: const TextStyle(fontSize: 11, color: AppTheme.grey)),
          ]),
          if (quiz['status'] == 'active') ...[
            const SizedBox(height: 10),
            ClipRRect(
              borderRadius: BorderRadius.circular(4),
              child: LinearProgressIndicator(
                value: total > 0 ? responses / total : 0,
                backgroundColor: AppTheme.green.withOpacity(0.1),
                valueColor: const AlwaysStoppedAnimation<Color>(AppTheme.green),
                minHeight: 6,
              ),
            ),
          ],
        ]),
      ),
    );
  }
}

class _QuizMeta extends StatelessWidget {
  final IconData icon;
  final String label;
  const _QuizMeta({required this.icon, required this.label});

  @override
  Widget build(BuildContext context) {
    return Row(children: [
      Icon(icon, size: 13, color: AppTheme.grey),
      const SizedBox(width: 4),
      Text(label, style: const TextStyle(fontSize: 11, color: AppTheme.grey)),
    ]);
  }
}

class QuizDetailScreen extends StatefulWidget {
  final Map<String, dynamic> quiz;
  const QuizDetailScreen({super.key, required this.quiz});
  @override
  State<QuizDetailScreen> createState() => _QuizDetailScreenState();
}

class _QuizDetailScreenState extends State<QuizDetailScreen> {
  final List<Map<String, dynamic>> _questions = [
    {
      'q': 'What is the discriminant of a quadratic equation?',
      'type': 'MCQ',
      'options': ['b²-4ac', 'b²+4ac', '-b/2a', '4ac-b²'],
      'answer': 0,
    },
    {
      'q': 'The quadratic formula gives how many solutions?',
      'type': 'MCQ',
      'options': ['Always 1', 'Always 2', 'Up to 2', 'Always 3'],
      'answer': 2,
    },
    {
      'q': 'A parabola opens upward when a > 0.',
      'type': 'TF',
      'answer': true,
    },
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.quiz['title'] as String),
        leading: CircleIconButton(icon: Icons.arrow_back_ios_new,
            onTap: () => Navigator.pop(context), size: 38),
        leadingWidth: 60,
        actions: [
          CircleIconButton(icon: Icons.add, onTap: () {}, size: 38),
          const SizedBox(width: 16),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 24),
        children: [
          GradientCard(
            colors: const [AppTheme.primary, AppTheme.secondary],
            child: Row(children: [
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(widget.quiz['class'] as String,
                    style: const TextStyle(color: Colors.white70, fontSize: 12)),
                Text(widget.quiz['title'] as String,
                    style: const TextStyle(color: Colors.white,
                        fontSize: 16, fontWeight: FontWeight.w800)),
                const SizedBox(height: 8),
                Row(children: [
                  _StatPill(label: '${widget.quiz['questions']} Qs'),
                  const SizedBox(width: 8),
                  _StatPill(label: widget.quiz['duration'] as String),
                ]),
              ])),
              const Icon(Icons.quiz_rounded, color: Colors.white54, size: 48),
            ]),
          ),
          const SizedBox(height: 20),
          SectionHeader(title: 'Questions (${_questions.length})',
              actionLabel: '+ Add', onAction: () {}),
          const SizedBox(height: 12),
          ..._questions.asMap().entries.map((e) {
            final q = e.value;
            return Container(
              margin: const EdgeInsets.only(bottom: 12),
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.04),
                    blurRadius: 6, offset: const Offset(0, 2))],
              ),
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Row(children: [
                  Container(
                    width: 28, height: 28,
                    decoration: BoxDecoration(
                        color: AppTheme.primary.withOpacity(0.1),
                        shape: BoxShape.circle),
                    child: Center(child: Text('${e.key + 1}',
                        style: const TextStyle(fontSize: 12,
                            fontWeight: FontWeight.w800, color: AppTheme.primary))),
                  ),
                  const SizedBox(width: 8),
                  PillBadge(label: q['type'] as String,
                      color: AppTheme.blue.withOpacity(0.1), textColor: AppTheme.blue),
                ]),
                const SizedBox(height: 10),
                Text(q['q'] as String,
                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                if (q['type'] == 'MCQ') ...[
                  const SizedBox(height: 10),
                  ...(q['options'] as List).asMap().entries.map((opt) => Padding(
                    padding: const EdgeInsets.only(bottom: 6),
                    child: Row(children: [
                      Container(
                        width: 20, height: 20,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: opt.key == q['answer']
                              ? AppTheme.green : AppTheme.lightBg,
                          border: Border.all(
                              color: opt.key == q['answer']
                                  ? AppTheme.green : AppTheme.grey.withOpacity(0.3)),
                        ),
                        child: opt.key == q['answer']
                            ? const Icon(Icons.check, size: 12, color: Colors.white)
                            : null,
                      ),
                      const SizedBox(width: 8),
                      Text(opt.value as String,
                          style: TextStyle(fontSize: 12,
                              color: opt.key == q['answer']
                                  ? AppTheme.green : null)),
                    ]),
                  )),
                ],
                if (q['type'] == 'TF')
                  Padding(
                    padding: const EdgeInsets.only(top: 8),
                    child: Row(children: [
                      _TFChip(label: 'True', selected: q['answer'] == true),
                      const SizedBox(width: 8),
                      _TFChip(label: 'False', selected: q['answer'] == false),
                    ]),
                  ),
              ]),
            ).animate().fadeIn(delay: (e.key * 60).ms);
          }),
        ],
      ),
    );
  }
}

class _StatPill extends StatelessWidget {
  final String label;
  const _StatPill({required this.label});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
          color: Colors.white24, borderRadius: BorderRadius.circular(20)),
      child: Text(label, style: const TextStyle(
          color: Colors.white, fontSize: 11, fontWeight: FontWeight.w600)),
    );
  }
}

class _TFChip extends StatelessWidget {
  final String label;
  final bool selected;
  const _TFChip({required this.label, required this.selected});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
      decoration: BoxDecoration(
        color: selected ? AppTheme.green : AppTheme.lightBg,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(label, style: TextStyle(
          fontSize: 12, fontWeight: FontWeight.w700,
          color: selected ? Colors.white : AppTheme.grey)),
    );
  }
}
