import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../providers/api_provider.dart';
import '../services/api_service.dart';
import '../widgets/common_widgets.dart';

class MeetingScreen extends StatefulWidget {
  const MeetingScreen({super.key});
  @override
  State<MeetingScreen> createState() => _MeetingScreenState();
}

class _MeetingScreenState extends State<MeetingScreen> {
  @override
  Widget build(BuildContext context) {
    final api = context.watch<ApiProvider>();
    final meetings = api.meetings;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Parent-Teacher Meetings'),
        leading: CircleIconButton(
          icon: Icons.arrow_back_ios_new,
          onTap: () => Navigator.pop(context),
          size: 38,
        ),
        leadingWidth: 60,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 40),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Schedule New Meeting Banner
            AppCard(
              padding: const EdgeInsets.all(16),
              color: AppTheme.primaryLight,
              child: Row(
                children: [
                  Container(
                    width: 48,
                    height: 48,
                    decoration: const BoxDecoration(
                      color: AppTheme.primary,
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.video_camera_front_outlined, color: Colors.white, size: 26),
                  ),
                  const SizedBox(width: 14),
                  const Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Book 1-on-1 Consultation', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w900, color: AppTheme.primaryDark)),
                        Text('Connect with teachers via video or in person', style: TextStyle(fontSize: 11, color: AppTheme.primaryDark)),
                      ],
                    ),
                  ),
                  ElevatedButton(
                    onPressed: () => _showBookMeetingSheet(context, api),
                    style: ElevatedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                    ),
                    child: const Text('Book', style: TextStyle(fontSize: 12)),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            const SectionHeader(title: '📅 Scheduled Meetings'),
            const SizedBox(height: 12),
            if (meetings.isEmpty)
              const EmptyStateWidget(
                icon: Icons.event_available_outlined,
                title: 'No meetings scheduled',
                subtitle: 'Tap the Book button above to request a consultation slot with a teacher.',
              )
            else
              ...meetings.map((m) => _MeetingCard(meeting: m)),
          ],
        ),
      ),
    );
  }

  void _showBookMeetingSheet(BuildContext context, ApiProvider api) {
    final titleCtrl = TextEditingController(text: 'Academic Progress Review');
    final agendaCtrl = TextEditingController();
    int? selectedTeacherId = api.teachers.isNotEmpty ? api.teachers.first.id : 1;
    String selectedDate = '2026-10-02';
    String selectedTime = '03:30 PM';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(28))),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setSheetState) => Padding(
          padding: EdgeInsets.fromLTRB(24, 24, 24, MediaQuery.of(ctx).viewInsets.bottom + 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Request Teacher Meeting', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
                  IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.pop(ctx)),
                ],
              ),
              const SizedBox(height: 14),
              const Text('Select Faculty Member:', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
              const SizedBox(height: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                decoration: BoxDecoration(
                  color: AppTheme.lightBg,
                  borderRadius: BorderRadius.circular(14),
                ),
                child: DropdownButtonHideUnderline(
                  child: DropdownButton<int>(
                    value: selectedTeacherId,
                    isExpanded: true,
                    items: api.teachers.map((t) {
                      return DropdownMenuItem<int>(
                        value: t.id,
                        child: Text('${t.name} (${t.subject})'),
                      );
                    }).toList(),
                    onChanged: (val) {
                      if (val != null) setSheetState(() => selectedTeacherId = val);
                    },
                  ),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: titleCtrl,
                decoration: InputDecoration(
                  labelText: 'Discussion Topic / Subject',
                  filled: true,
                  fillColor: AppTheme.lightBg,
                ),
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(color: AppTheme.lightBg, borderRadius: BorderRadius.circular(14)),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Date', style: TextStyle(fontSize: 10, color: AppTheme.grey)),
                          Text(selectedDate, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800)),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(color: AppTheme.lightBg, borderRadius: BorderRadius.circular(14)),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Time Slot', style: TextStyle(fontSize: 10, color: AppTheme.grey)),
                          Text(selectedTime, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800)),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              TextField(
                controller: agendaCtrl,
                maxLines: 2,
                decoration: InputDecoration(
                  labelText: 'Specific Agenda or Questions (Optional)',
                  filled: true,
                  fillColor: AppTheme.lightBg,
                ),
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: () async {
                    Navigator.pop(ctx);
                    await ApiService.requestMeeting({
                      'teacher_id': selectedTeacherId,
                      'topic': titleCtrl.text,
                      'date': selectedDate,
                      'time': selectedTime,
                      'agenda': agendaCtrl.text,
                    });
                    if (context.mounted) {
                      context.read<ApiProvider>().loadChildSpecificData();
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                          content: Text('Meeting request sent to teacher for confirmation!'),
                          backgroundColor: AppTheme.green,
                        ),
                      );
                    }
                  },
                  child: const Text('Submit Meeting Request'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _MeetingCard extends StatelessWidget {
  final MeetingItem meeting;
  const _MeetingCard({required this.meeting});

  @override
  Widget build(BuildContext context) {
    final isConfirmed = meeting.status.toLowerCase() == 'confirmed';

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      child: AppCard(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: AppTheme.primaryLight,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    meeting.meetingType,
                    style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: AppTheme.primary),
                  ),
                ),
                StatusBadge(
                  label: isConfirmed ? 'CONFIRMED' : 'PENDING APPROVAL',
                  color: isConfirmed ? AppTheme.green : AppTheme.orange,
                ),
              ],
            ),
            const SizedBox(height: 10),
            Text(meeting.title, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w900)),
            const SizedBox(height: 2),
            Text('${meeting.teacherName} • ${meeting.subject}', style: const TextStyle(fontSize: 12, color: AppTheme.grey)),
            const Divider(height: 20),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    const Icon(Icons.calendar_month_outlined, size: 14, color: AppTheme.grey),
                    const SizedBox(width: 4),
                    Text(meeting.date, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800)),
                  ],
                ),
                Row(
                  children: [
                    const Icon(Icons.access_time, size: 14, color: AppTheme.grey),
                    const SizedBox(width: 4),
                    Text('${meeting.time} (${meeting.duration})', style: const TextStyle(fontSize: 11, color: AppTheme.grey)),
                  ],
                ),
              ],
            ),
            if (meeting.meetLink != null && isConfirmed) ...[
              const SizedBox(height: 12),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: () {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text('Opening meeting room: ${meeting.meetLink}')),
                    );
                  },
                  icon: const Icon(Icons.video_call_rounded, size: 18),
                  label: const Text('Join Video Meeting'),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
