import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../widgets/common_widgets.dart';
import '../services/api_service.dart';
import '../providers/teacher_provider.dart';
import '../data/app_data.dart';

class ClassInchargeHubScreen extends StatefulWidget {
  const ClassInchargeHubScreen({super.key});

  @override
  State<ClassInchargeHubScreen> createState() => _ClassInchargeHubScreenState();
}

class _ClassInchargeHubScreenState extends State<ClassInchargeHubScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tab;
  final ApiService _apiService = ApiService();

  final List<Map<String, dynamic>> _studentLeaves = [
    {
      'id': 1,
      'name': 'Aarav Sharma',
      'roll': '04',
      'avatar': 'AS',
      'dates': '2081-06-18 to 2081-06-20 (3 Days)',
      'reason': 'Suffering from typhoid fever, doctor advised 3 days bed rest.',
      'parent': 'Ramesh Sharma (+977 9841234567)',
      'status': 'Pending',
    },
    {
      'id': 2,
      'name': 'Suman Shrestha',
      'roll': '12',
      'avatar': 'SS',
      'dates': '2081-06-19 (1 Day)',
      'reason': 'Family religious ceremony at ancestral home (Kul Puja).',
      'parent': 'Gita Shrestha (+977 9851098765)',
      'status': 'Pending',
    },
    {
      'id': 3,
      'name': 'Pooja Thapa',
      'roll': '21',
      'avatar': 'PT',
      'dates': '2081-06-10 to 2081-06-11 (2 Days)',
      'reason': 'Participating in District Level Taekwondo Championship.',
      'parent': 'Bikram Thapa (+977 9860112233)',
      'status': 'Approved',
    },
  ];

  final List<Map<String, dynamic>> _mySectionStudents = [
    {
      'roll': '01',
      'name': 'Aayush Adhikari',
      'attendance': '96%',
      'gpa': '3.92',
      'parent': 'Kishor Adhikari',
      'phone': '9841001122',
      'feeStatus': 'Paid',
    },
    {
      'roll': '02',
      'name': 'Ananya Joshi',
      'attendance': '92%',
      'gpa': '3.85',
      'parent': 'Deepak Joshi',
      'phone': '9841334455',
      'feeStatus': 'Paid',
    },
    {
      'roll': '03',
      'name': 'Bibek KC',
      'attendance': '74%',
      'gpa': '3.10',
      'parent': 'Mohan KC',
      'phone': '9851778899',
      'feeStatus': 'Due 1 Month',
      'isLowAttendance': true,
    },
    {
      'roll': '04',
      'name': 'Aarav Sharma',
      'attendance': '88%',
      'gpa': '3.65',
      'parent': 'Ramesh Sharma',
      'phone': '9841234567',
      'feeStatus': 'Paid',
    },
    {
      'roll': '05',
      'name': 'Diya Maharjan',
      'attendance': '98%',
      'gpa': '4.00',
      'parent': 'Sanjay Maharjan',
      'phone': '9860998877',
      'feeStatus': 'Paid',
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

  void _postDiaryModal() {
    final topicCtrl = TextEditingController();
    final homeworkCtrl = TextEditingController();
    final noticeCtrl = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => Padding(
        padding: EdgeInsets.fromLTRB(
            24, 24, 24, MediaQuery.of(ctx).viewInsets.bottom + 24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(children: [
              Icon(Icons.menu_book_rounded, color: AppTheme.primary),
              SizedBox(width: 8),
              Text('Post Class Diary (कक्षा डायरी)',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800)),
            ]),
            const SizedBox(height: 16),
            TextField(
              controller: topicCtrl,
              decoration: InputDecoration(
                labelText: 'Topics Covered Today',
                hintText: 'e.g. Science Ch.4 Energy, Math Ex 3.2',
                filled: true,
                fillColor: AppTheme.lightBg,
                border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: BorderSide.none),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: homeworkCtrl,
              maxLines: 2,
              decoration: InputDecoration(
                labelText: 'Homework Assigned',
                hintText: 'e.g. Complete Q1 to Q8 on page 45',
                filled: true,
                fillColor: AppTheme.lightBg,
                border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: BorderSide.none),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: noticeCtrl,
              decoration: InputDecoration(
                labelText: 'Special Note to Parents (Optional)',
                hintText: 'e.g. Please send geometry box tomorrow',
                filled: true,
                fillColor: AppTheme.lightBg,
                border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: BorderSide.none),
              ),
            ),
            const SizedBox(height: 20),
            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.primary,
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14)),
                ),
                onPressed: () {
                  Navigator.pop(ctx);
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      backgroundColor: AppTheme.green,
                      content: Text('Daily diary broadcasted to all parents!'),
                    ),
                  );
                },
                child: const Text('Broadcast Diary to Parents',
                    style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final teacher = Provider.of<TeacherProvider>(context);
    final inchargeClass = teacher.teacherClasses.isNotEmpty
        ? (teacher.teacherClasses.firstWhere((c) => c.isClassTeacher, orElse: () => teacher.teacherClasses.first))
        : TeacherClass(id: 0, name: 'Assigned Class', section: 'A', room: 'Classroom', subject: 'General', time: '09:00 AM', studentsCount: teacher.studentsCount, isClassTeacher: true);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Class In-Charge (कक्षा शिक्षक)'),
        leading: CircleIconButton(
          icon: Icons.arrow_back_ios_new,
          onTap: () => Navigator.pop(context),
          size: 38,
        ),
        leadingWidth: 60,
        actions: [
          IconButton(
            icon: const Icon(Icons.post_add_rounded, color: AppTheme.primary),
            tooltip: 'Post Daily Diary',
            onPressed: _postDiaryModal,
          ),
          const SizedBox(width: 8),
        ],
        bottom: TabBar(
          controller: _tab,
          labelColor: AppTheme.primary,
          unselectedLabelColor: AppTheme.grey,
          indicatorColor: AppTheme.primary,
          indicatorSize: TabBarIndicatorSize.label,
          tabs: const [
            Tab(text: 'Overview'),
            Tab(text: 'Leave Requests (2)'),
            Tab(text: 'Class Students'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tab,
        children: [
          _buildOverviewTab(inchargeClass, teacher),
          _buildLeavesTab(),
          _buildStudentsTab(teacher),
        ],
      ),
    );
  }

  Widget _buildOverviewTab(TeacherClass inchargeClass, TeacherProvider teacher) {
    final count = inchargeClass.studentsCount > 0 ? inchargeClass.studentsCount : (teacher.studentsCount > 0 ? teacher.studentsCount : 30);
    return SingleChildScrollView(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 100),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Banner
          GradientCard(
            colors: const [AppTheme.primary, AppTheme.secondary],
            padding: const EdgeInsets.all(18),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.white24,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Text('ASSIGNED IN-CHARGE',
                        style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w800)),
                  ),
                  const Text('Academic Year 2081',
                      style: TextStyle(color: Colors.white70, fontSize: 11)),
                ],
              ),
              const SizedBox(height: 10),
              Text(inchargeClass.name,
                  style: const TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.w900)),
              Text('Room ${inchargeClass.room} • Total $count Enrolled Students',
                  style: const TextStyle(color: Colors.white70, fontSize: 12)),
              const SizedBox(height: 16),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  _InchargeMiniStat(label: 'Today Attendance', value: '${(count * 0.95).round()}/$count (95%)'),
                  const _InchargeMiniStat(label: 'Pending Leaves', value: '2 Requests'),
                  const _InchargeMiniStat(label: 'Class Rank Avg', value: '3.62 GPA'),
                ],
              ),
            ]),
          ),
          const SizedBox(height: 20),

          // Action Shortcuts
          const SectionHeader(title: 'Quick Actions'),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: _ActionBox(
                  icon: Icons.menu_book_rounded,
                  label: 'Post Daily Diary',
                  color: AppTheme.blue,
                  onTap: _postDiaryModal,
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: _ActionBox(
                  icon: Icons.how_to_reg_rounded,
                  label: 'Today Attendance',
                  color: AppTheme.green,
                  onTap: () {},
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: _ActionBox(
                  icon: Icons.campaign_outlined,
                  label: 'Parent Alert',
                  color: AppTheme.orange,
                  onTap: () {},
                ),
              ),
            ],
          ),
          const SizedBox(height: 24),

          // Low Attendance Alerts in this section
          const SectionHeader(title: '⚠️ Attention Needed (Attendance < 75%)'),
          const SizedBox(height: 12),
          AppCard(
            padding: const EdgeInsets.all(14),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: Colors.red.shade50,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.warning_amber_rounded, color: Colors.red),
                ),
                const SizedBox(width: 12),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Bibek KC (Roll #03)',
                          style: TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
                      Text('Attendance dropped to 74% • Fee due 1 month',
                          style: TextStyle(color: Colors.red, fontSize: 11)),
                    ],
                  ),
                ),
                TextButton(
                  onPressed: () {},
                  child: const Text('Call Parent',
                      style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12, color: AppTheme.primary)),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLeavesTab() {
    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 100),
      itemCount: _studentLeaves.length,
      itemBuilder: (ctx, i) {
        final leave = _studentLeaves[i];
        final isPending = leave['status'] == 'Pending';

        return AppCard(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(children: [
                Container(
                  width: 40,
                  height: 40,
                  decoration: const BoxDecoration(
                    color: AppTheme.primaryLight,
                    shape: BoxShape.circle,
                  ),
                  child: Center(
                    child: Text(
                      leave['avatar'] as String,
                      style: const TextStyle(fontWeight: FontWeight.w800, color: AppTheme.primary),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('${leave['name']} (Roll #${leave['roll']})',
                          style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14)),
                      Text(leave['dates'] as String,
                          style: const TextStyle(fontSize: 11, color: AppTheme.grey)),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: isPending ? Colors.orange.shade50 : Colors.green.shade50,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(
                    leave['status'] as String,
                    style: TextStyle(
                      color: isPending ? Colors.orange : Colors.green,
                      fontWeight: FontWeight.w700,
                      fontSize: 11,
                    ),
                  ),
                ),
              ]),
              const SizedBox(height: 12),
              Text('Reason: ${leave['reason']}',
                  style: const TextStyle(fontSize: 12, color: AppTheme.dark, height: 1.3)),
              const SizedBox(height: 6),
              Text('Parent Contact: ${leave['parent']}',
                  style: const TextStyle(fontSize: 11, color: AppTheme.grey)),
              if (isPending) ...[
                const SizedBox(height: 12),
                const Divider(height: 1),
                const SizedBox(height: 8),
                Row(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    TextButton(
                      onPressed: () {
                        setState(() => leave['status'] = 'Rejected');
                      },
                      child: const Text('Reject', style: TextStyle(color: Colors.red)),
                    ),
                    const SizedBox(width: 8),
                    ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.green,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                      onPressed: () {
                        setState(() => leave['status'] = 'Approved');
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Leave Approved & SMS sent to parent!')),
                        );
                      },
                      child: const Text('Approve Leave'),
                    ),
                  ],
                ),
              ],
            ],
          ),
        ).animate().fadeIn(delay: (i * 60).ms);
      },
    );
  }

  Widget _buildStudentsTab(TeacherProvider teacher) {
    final students = teacher.studentList;
    if (students.isEmpty && _mySectionStudents.isEmpty) {
      return const Center(child: Text('No students found in your section'));
    }

    final count = students.isNotEmpty ? students.length : _mySectionStudents.length;

    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 100),
      itemCount: count,
      itemBuilder: (ctx, i) {
        if (students.isNotEmpty) {
          final s = students[i];
          final roll = s.rollNumber.isNotEmpty ? s.rollNumber : (i + 1).toString().padLeft(2, '0');
          final attRate = s.attendanceRate > 0 ? '${s.attendanceRate.toInt()}%' : '95%';
          final parent = s.parentName.isNotEmpty ? s.parentName : 'Parent';
          final phone = s.parentPhone.isNotEmpty ? s.parentPhone : 'N/A';

          return AppCard(
            padding: const EdgeInsets.all(14),
            child: Row(
              children: [
                Container(
                  width: 36,
                  height: 36,
                  decoration: const BoxDecoration(
                    color: AppTheme.lightBg,
                    shape: BoxShape.circle,
                  ),
                  child: Center(
                    child: Text(roll,
                        style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 12)),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(s.name,
                          style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
                      Text('Parent: $parent • $phone',
                          style: const TextStyle(fontSize: 11, color: AppTheme.grey)),
                    ],
                  ),
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text('Att: $attRate',
                        style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w700,
                            color: s.attendanceRate > 0 && s.attendanceRate < 80 ? Colors.red : AppTheme.green)),
                    Text(s.grade,
                        style: const TextStyle(
                            fontSize: 10,
                            color: AppTheme.grey,
                            fontWeight: FontWeight.w600)),
                  ],
                ),
              ],
            ),
          ).animate().fadeIn(delay: (i * 40).ms);
        }

        final s = _mySectionStudents[i];
        return AppCard(
          padding: const EdgeInsets.all(14),
          child: Row(
            children: [
              Container(
                width: 36,
                height: 36,
                decoration: const BoxDecoration(
                  color: AppTheme.lightBg,
                  shape: BoxShape.circle,
                ),
                child: Center(
                  child: Text(s['roll'] as String,
                      style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 12)),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(s['name'] as String,
                        style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
                    Text('Parent: ${s['parent']} • ${s['phone']}',
                        style: const TextStyle(fontSize: 11, color: AppTheme.grey)),
                  ],
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text('Att: ${s['attendance']}',
                      style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                          color: s['isLowAttendance'] == true ? Colors.red : AppTheme.green)),
                  Text(s['feeStatus'] as String,
                      style: TextStyle(
                          fontSize: 10,
                          color: s['feeStatus'] == 'Paid' ? AppTheme.grey : Colors.orange,
                          fontWeight: FontWeight.w600)),
                ],
              ),
            ],
          ),
        ).animate().fadeIn(delay: (i * 40).ms);
      },
    );
  }
}

class _InchargeMiniStat extends StatelessWidget {
  final String label, value;
  const _InchargeMiniStat({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(value,
            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 12)),
        Text(label, style: const TextStyle(color: Colors.white60, fontSize: 10)),
      ],
    );
  }
}

class _ActionBox extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;

  const _ActionBox({
    required this.icon,
    required this.label,
    required this.color,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(14),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 8),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.04),
              blurRadius: 8,
              offset: const Offset(0, 2),
            )
          ],
        ),
        child: Column(
          children: [
            Icon(icon, color: color, size: 24),
            const SizedBox(height: 6),
            Text(
              label,
              textAlign: TextAlign.center,
              style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 11, color: AppTheme.dark),
            ),
          ],
        ),
      ),
    );
  }
}
