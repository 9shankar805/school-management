import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../widgets/common_widgets.dart';
import '../services/api_service.dart';
import '../providers/teacher_provider.dart';

class QuestionPaperGeneratorScreen extends StatefulWidget {
  const QuestionPaperGeneratorScreen({super.key});

  @override
  State<QuestionPaperGeneratorScreen> createState() =>
      _QuestionPaperGeneratorScreenState();
}

class _QuestionPaperGeneratorScreenState
    extends State<QuestionPaperGeneratorScreen> {
  final ApiService _apiService = ApiService();
  bool _generating = false;

  String _selectedClass = 'Grade 10 (SEE Batch)';
  String _selectedSubject = 'Compulsory Mathematics';
  String _selectedExamType = 'First Terminal Examination 2081';
  int _fullMarks = 100;
  int _passMarks = 40;
  String _duration = '3 Hours';

  final List<Map<String, dynamic>> _sections = [
    {
      'name': 'Section A (Very Short / Knowledge & Understanding)',
      'marksPerQuestion': 1,
      'questionCount': 10,
      'total': 10,
      'taxonomy': 'KNOWLEDGE',
      'questions': [
        'Define a quadratic equation and state its standard form ax² + bx + c = 0.',
        'If universal set U = {1..10} and set A = {2, 4, 6, 8}, find the cardinality of complement A\'.',
        'State the formula to compute compound interest compounded semi-annually.',
        'Write the value of sin²θ + cos²θ.',
      ]
    },
    {
      'name': 'Section B (Short Answer / Application)',
      'marksPerQuestion': 2,
      'questionCount': 10,
      'total': 20,
      'taxonomy': 'APPLICATION',
      'questions': [
        'Solve the quadratic equation by factoring: 2x² - 7x + 3 = 0.',
        'If the third term of an arithmetic sequence is 9 and seventh term is 25, find the 20th term.',
        'Prove that in an isosceles triangle, the median drawn to the base is perpendicular to the base.',
      ]
    },
    {
      'name': 'Section C (Analytical / High Order Thinking)',
      'marksPerQuestion': 4,
      'questionCount': 10,
      'total': 40,
      'taxonomy': 'ANALYSIS',
      'questions': [
        'A merchant marked an electronic item 25% above cost price. If he allowed 10% discount and added 13% VAT, the selling price became Rs. 5,650. Find the cost price.',
        'Construct a triangle ABC where AB = 5cm, BC = 6cm, and ∠B = 60°, then construct a parallelogram equal in area to ΔABC having one angle 75°.',
      ]
    },
    {
      'name': 'Section D (Problem Solving / Creation)',
      'marksPerQuestion': 5,
      'questionCount': 6,
      'total': 30,
      'taxonomy': 'EVALUATION',
      'questions': [
        'Solve the simultaneous linear-quadratic equations graphically: y = x² - 4 and y = 2x - 1.',
        'From the roof of a 20m high school building, the angles of elevation and depression of the top and bottom of a communication tower are 45° and 30° respectively. Find the height of the tower.',
      ]
    },
  ];

  void _triggerAiGeneration() async {
    setState(() => _generating = true);

    try {
      await Future.delayed(const Duration(milliseconds: 1200));
      if (!mounted) return;
      setState(() => _generating = false);

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          backgroundColor: AppTheme.green,
          content: Text(
            'AI Question Paper generated with CDC 2078 Blueprint & Answer Key ✅',
          ),
        ),
      );
    } catch (_) {
      if (mounted) setState(() => _generating = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final teacher = Provider.of<TeacherProvider>(context);

    final classOptions = teacher.teacherClasses.isNotEmpty
        ? teacher.teacherClasses.map((c) => c.name).toSet().toList()
        : ['Grade 8 (BLE Pattern)', 'Grade 9', 'Grade 10 (SEE Batch)', 'Grade 11 (NEB)', 'Grade 12 (NEB)'];

    final subjectOptions = teacher.teacherClasses.isNotEmpty
        ? teacher.teacherClasses.map((c) => c.subject).where((s) => s.isNotEmpty).toSet().toList()
        : ['Compulsory Mathematics', 'Optional Mathematics', 'Science & Technology', 'Compulsory English', 'Social Studies'];

    final currentClass = classOptions.contains(_selectedClass) ? _selectedClass : classOptions.first;
    final currentSubject = subjectOptions.contains(_selectedSubject) ? _selectedSubject : subjectOptions.first;

    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
      appBar: AppBar(
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'AI Question Studio (प्रश्नपत्र)',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900),
            ),
            Text(
              'CDC 2078 Nepal Curriculum Blueprint',
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
          IconButton(
            icon: const Icon(Icons.print_rounded, color: AppTheme.primary),
            tooltip: 'Export Printable PDF',
            onPressed: () {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  backgroundColor: AppTheme.primary,
                  content: Text('Exporting CDC 2078 School Watermarked PDF... 🖨️'),
                ),
              );
            },
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: SingleChildScrollView(
        physics: const BouncingScrollPhysics(),
        padding: const EdgeInsets.fromLTRB(18, 4, 18, 90),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Banner
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
                    color: const Color(0xFF0F4C81).withOpacity(0.35),
                    blurRadius: 14,
                    offset: const Offset(0, 6),
                  ),
                ],
              ),
              child: Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: Colors.white.withOpacity(0.2),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: const Text(
                            'CDC NEPAL SPECIFICATION GRID',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 9.5,
                              fontWeight: FontWeight.w900,
                              letterSpacing: 0.5,
                            ),
                          ),
                        ),
                        const SizedBox(height: 8),
                        const Text(
                          'Bloom\'s Taxonomy AI Generator',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 17,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Automates terminal exams, model test sets, and answer rubrics compliant with SEE/NEB guidelines.',
                          style: TextStyle(color: Colors.white.withOpacity(0.8), fontSize: 11.5),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 10),
                  Container(
                    width: 48,
                    height: 48,
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.18),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.auto_awesome_rounded, color: Colors.white, size: 24),
                  ),
                ],
              ),
            ).animate().fadeIn().slideY(begin: 0.05, end: 0),
            const SizedBox(height: 18),

            // Controls
            const SectionHeader(
              title: '1. Paper Configuration (प्रश्नपत्र ढाँचा)',
              subtitle: 'Grade level, curriculum weightage and timing',
            ),
            const SizedBox(height: 10),
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: isDark ? AppTheme.darkCard : Colors.white,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(
                  color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
                  width: 1,
                ),
              ),
              child: Column(
                children: [
                  _buildDropdown(
                    'Class / Grade Level',
                    currentClass,
                    classOptions,
                    (v) => setState(() => _selectedClass = v!),
                    isDark,
                  ),
                  const SizedBox(height: 10),
                  _buildDropdown(
                    'Subject',
                    currentSubject,
                    subjectOptions,
                    (v) => setState(() => _selectedSubject = v!),
                    isDark,
                  ),
                  const SizedBox(height: 10),
                  _buildDropdown(
                    'Exam Category',
                    _selectedExamType,
                    [
                      'First Terminal Examination 2081',
                      'Second Terminal Examination 2081',
                      'Pre-Board / Sent-Up Exam',
                      'Monthly Unit Assessment',
                    ],
                    (v) => setState(() => _selectedExamType = v!),
                    isDark,
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: _buildNumberInput('Full Marks', '$_fullMarks', (val) {
                          setState(() => _fullMarks = int.tryParse(val) ?? 100);
                        }, isDark),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: _buildNumberInput('Pass Marks', '$_passMarks', (val) {
                          setState(() => _passMarks = int.tryParse(val) ?? 40);
                        }, isDark),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: _buildDropdown(
                          'Duration',
                          _duration,
                          ['1.5 Hours', '2 Hours', '2.5 Hours', '3 Hours'],
                          (v) => setState(() => _duration = v!),
                          isDark,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // AI Action Button
            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton.icon(
                onPressed: _generating ? null : _triggerAiGeneration,
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.primary,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                  elevation: 4,
                ),
                icon: _generating
                    ? const SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                      )
                    : const Icon(Icons.auto_awesome_rounded, size: 20),
                label: Text(
                  _generating ? 'Generating CDC Standard Blueprint...' : 'Generate Question Paper with AI ✨',
                  style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5),
                ),
              ),
            ),
            const SizedBox(height: 22),

            // Sections
            const SectionHeader(
              title: '2. Section Blueprint Preview (प्रश्नपत्र पूर्वावलोकन)',
              subtitle: 'Questions categorized by Bloom\'s cognitive taxonomy',
            ),
            const SizedBox(height: 10),
            ..._sections.map((sec) => _buildSectionCard(sec, isDark)),
          ],
        ),
      ),
    );
  }

  Widget _buildDropdown(
      String label, String value, List<String> items, ValueChanged<String?> onChanged, bool isDark) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppTheme.darkSubtext),
        ),
        const SizedBox(height: 4),
        DropdownButtonFormField<String>(
          value: items.contains(value) ? value : items.first,
          decoration: InputDecoration(
            filled: true,
            fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide.none,
            ),
            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
          ),
          items: items
              .map((item) => DropdownMenuItem(
                    value: item,
                    child: Text(item, style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700)),
                  ))
              .toList(),
          onChanged: onChanged,
        ),
      ],
    );
  }

  Widget _buildNumberInput(String label, String value, ValueChanged<String> onChanged, bool isDark) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppTheme.darkSubtext),
        ),
        const SizedBox(height: 4),
        TextFormField(
          initialValue: value,
          keyboardType: TextInputType.number,
          decoration: InputDecoration(
            filled: true,
            fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide.none,
            ),
            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
          ),
          onChanged: onChanged,
        ),
      ],
    );
  }

  Widget _buildSectionCard(Map<String, dynamic> sec, bool isDark) {
    final questions = sec['questions'] as List<dynamic>;
    final taxonomy = sec['taxonomy'] as String;

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
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
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  sec['name'] as String,
                  style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w900),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: AppTheme.primary.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  '${sec['total']} Marks',
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w900,
                    color: AppTheme.primary,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(
                  color: AppTheme.purple.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  'Taxonomy: $taxonomy',
                  style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: AppTheme.purple),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Divider(color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder, height: 1),
          const SizedBox(height: 8),
          ...questions.asMap().entries.map((q) => Padding(
                padding: const EdgeInsets.symmetric(vertical: 4),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Q${q.key + 1}. ',
                      style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 12.5),
                    ),
                    Expanded(
                      child: Text(
                        q.value.toString(),
                        style: TextStyle(
                          fontSize: 12.5,
                          color: isDark ? Colors.white70 : AppTheme.dark,
                          height: 1.35,
                        ),
                      ),
                    ),
                  ],
                ),
              )),
        ],
      ),
    );
  }
}
