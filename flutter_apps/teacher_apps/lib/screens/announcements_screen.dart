import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../widgets/common_widgets.dart';
import '../providers/auth_provider.dart';

class AnnouncementsScreen extends StatefulWidget {
  const AnnouncementsScreen({super.key});
  @override
  State<AnnouncementsScreen> createState() => _AnnouncementsScreenState();
}

class _AnnouncementsScreenState extends State<AnnouncementsScreen> {
  int _selectedCategoryIndex = 0;
  String _searchQuery = '';
  final TextEditingController _searchCtrl = TextEditingController();

  final List<String> _categories = [
    'All Notices',
    'Exam Routine',
    'Administration',
    'Academic',
    'Emergency 🚨',
  ];

  final List<Map<String, dynamic>> _announcements = [
    {
      'id': '1',
      'title': 'Dashain & Tihar Vacation Notice २०८१',
      'body':
          'Dear Faculty & Students, the school will remain closed from Ashwin 14 to Kartik 20 for Dashain, Tihar & Chhath festivals. Faculty are requested to submit terminal exam marks before Ashwin 13.',
      'nepaliDate': '१० असोज २०८१ (10:30 AM)',
      'author': 'Office of the Principal',
      'designation': 'Principal',
      'target': 'School Wide • All Staff',
      'category': 'Academic',
      'pinned': true,
      'isUrgent': true,
      'attachment': 'Dashain_Vacation_Schedule_2081.pdf (1.2 MB)',
      'color': AppTheme.red,
    },
    {
      'id': '2',
      'title': 'Second Terminal Exam Routine & Question Paper Submission',
      'body':
          'All subject teachers are hereby instructed to submit 2 sets of question papers along with marking schemes to the Exam Controller by 5th Ashwin 2081.',
      'nepaliDate': '०८ असोज २०८१ (02:15 PM)',
      'author': 'Exam Controller Division',
      'designation': 'Exam Dept',
      'target': 'All Subject Teachers',
      'category': 'Exam Routine',
      'pinned': true,
      'isUrgent': false,
      'attachment': 'Terminal_Exam_Blueprint_Grade10_12.pdf (2.4 MB)',
      'color': AppTheme.primary,
    },
    {
      'id': '3',
      'title': 'Faculty Development Workshop on AI in Education',
      'body':
          'A special hybrid seminar on "Integrating Smart AI tools in High School STEM" will be conducted this Saturday from 11:00 AM in the Audio-Visual Hall.',
      'nepaliDate': '०५ असोज २०८१ (09:00 AM)',
      'author': 'Academic Excellence Cell',
      'designation': 'Training Head',
      'target': 'High School Faculty (Grade 8-12)',
      'category': 'Administration',
      'pinned': false,
      'isUrgent': false,
      'attachment': null,
      'color': AppTheme.purple,
    },
    {
      'id': '4',
      'title': 'Parent-Teacher Meeting (PTM) for Grade 10 SEE Batch',
      'body':
          'Parent-Teacher conference for Grade 10 A & B is scheduled for this Friday. Teachers are requested to have updated attendance records and unit test marks ready.',
      'nepaliDate': '०२ असोज २०८१ (04:00 PM)',
      'author': 'Administration Department',
      'designation': 'Vice Principal',
      'target': 'Grade 10 Faculty & Mentors',
      'category': 'Administration',
      'pinned': false,
      'isUrgent': false,
      'attachment': 'PTM_Time_Slots_Batch2081.pdf (850 KB)',
      'color': AppTheme.orange,
    },
    {
      'id': '5',
      'title': 'Science Exhibition & Robotics Showcase 2081',
      'body':
          'Annual Science Expo registrations are open. Science and Computer science mentors are requested to guide selected student projects for intra-school robotics competition.',
      'nepaliDate': '२८ भदौ २०८१ (11:30 AM)',
      'author': 'Science Club Coordinator',
      'designation': 'Science Dept',
      'target': 'Science & IT Department',
      'category': 'Academic',
      'pinned': false,
      'isUrgent': false,
      'attachment': null,
      'color': AppTheme.teal,
    },
  ];

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final selectedCat = _categories[_selectedCategoryIndex];

    final filteredNotices = _announcements.where((a) {
      final matchesCategory = _selectedCategoryIndex == 0 ||
          (selectedCat == 'Emergency 🚨'
              ? a['isUrgent'] == true
              : a['category'] == selectedCat);

      if (_searchQuery.isEmpty) return matchesCategory;

      final q = _searchQuery.toLowerCase();
      final title = (a['title'] as String).toLowerCase();
      final body = (a['body'] as String).toLowerCase();
      final author = (a['author'] as String).toLowerCase();

      return matchesCategory &&
          (title.contains(q) || body.contains(q) || author.contains(q));
    }).toList();

    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
      appBar: AppBar(
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Notice Board (सूचना पाटी)',
              style: TextStyle(fontSize: 16.5, fontWeight: FontWeight.w900),
            ),
            Text(
              'Nepal School Hub Digital Circulars • 2081',
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w600,
                color: AppTheme.darkSubtext,
              ),
            ),
          ],
        ),
        leading: Navigator.canPop(context)
            ? CircleIconButton(
                icon: Icons.arrow_back_ios_new_rounded,
                onTap: () => Navigator.pop(context),
                size: 38,
              )
            : null,
        leadingWidth: Navigator.canPop(context) ? 60 : null,
        automaticallyImplyLeading: false,
        actions: [
          Container(
            margin: const EdgeInsets.only(right: 14),
            child: ElevatedButton.icon(
              onPressed: () => _showComposeSheet(context, isDark),
              icon: const Icon(Icons.edit_note_rounded, size: 18),
              label: const Text(
                'Publish',
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primary,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
          ),
        ],
      ),
      body: Column(
        children: [
          // Search & Filter Bar
          Padding(
            padding: const EdgeInsets.fromLTRB(18, 6, 18, 8),
            child: TextField(
              controller: _searchCtrl,
              onChanged: (v) => setState(() => _searchQuery = v),
              decoration: InputDecoration(
                hintText: 'Search circulars, routines, memos...',
                hintStyle:
                    const TextStyle(fontSize: 12.5, color: AppTheme.darkSubtext),
                prefixIcon: const Icon(
                  Icons.search_rounded,
                  size: 18,
                  color: AppTheme.darkSubtext,
                ),
                suffixIcon: _searchQuery.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear, size: 16),
                        onPressed: () {
                          _searchCtrl.clear();
                          setState(() => _searchQuery = '');
                        },
                      )
                    : null,
                filled: true,
                fillColor: isDark ? AppTheme.darkCard : Colors.white,
                contentPadding: const EdgeInsets.symmetric(vertical: 10),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: BorderSide(
                    color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
                  ),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: BorderSide(
                    color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
                  ),
                ),
              ),
            ),
          ),

          // Categories Horizontal Selector
          SizedBox(
            height: 40,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 18),
              itemCount: _categories.length,
              itemBuilder: (_, i) {
                final sel = i == _selectedCategoryIndex;
                final name = _categories[i];
                return GestureDetector(
                  onTap: () => setState(() => _selectedCategoryIndex = i),
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 200),
                    margin: const EdgeInsets.only(right: 8),
                    padding:
                        const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                    decoration: BoxDecoration(
                      gradient: sel
                          ? const LinearGradient(
                              colors: [Color(0xFF0F4C81), Color(0xFF2563EB)],
                            )
                          : null,
                      color: sel
                          ? null
                          : (isDark ? AppTheme.darkCard : Colors.white),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: sel
                            ? AppTheme.primary
                            : (isDark
                                ? AppTheme.darkCardBorder
                                : AppTheme.cardBorder),
                        width: 1,
                      ),
                      boxShadow: sel
                          ? [
                              BoxShadow(
                                color: AppTheme.primary.withOpacity(0.25),
                                blurRadius: 6,
                                offset: const Offset(0, 2),
                              ),
                            ]
                          : null,
                    ),
                    child: Center(
                      child: Text(
                        name,
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight:
                              sel ? FontWeight.w800 : FontWeight.w600,
                          color: sel
                              ? Colors.white
                              : (isDark ? Colors.white70 : AppTheme.dark),
                        ),
                      ),
                    ),
                  ),
                );
              },
            ),
          ),
          const SizedBox(height: 10),

          // Notices List
          Expanded(
            child: filteredNotices.isEmpty
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.feed_outlined,
                          size: 48,
                          color: AppTheme.darkSubtext.withOpacity(0.5),
                        ),
                        const SizedBox(height: 12),
                        const Text(
                          'No announcements in this category',
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w700,
                            color: AppTheme.darkSubtext,
                          ),
                        ),
                      ],
                    ),
                  )
                : ListView.builder(
                    physics: const BouncingScrollPhysics(),
                    padding: const EdgeInsets.fromLTRB(18, 4, 18, 90),
                    itemCount: filteredNotices.length,
                    itemBuilder: (context, i) {
                      final item = filteredNotices[i];
                      return _buildNoticeCard(item, isDark, i)
                          .animate()
                          .fadeIn(delay: (i * 40).ms)
                          .slideY(begin: 0.05, end: 0, duration: 250.ms);
                    },
                  ),
          ),
        ],
      ),
    );
  }

  Widget _buildNoticeCard(Map<String, dynamic> item, bool isDark, int index) {
    final isUrgent = item['isUrgent'] == true;
    final isPinned = item['pinned'] == true;
    final color = item['color'] as Color;
    final hasAttachment = item['attachment'] != null;

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkCard : Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(
          color: isUrgent
              ? AppTheme.red.withOpacity(0.5)
              : (isPinned
                  ? AppTheme.primary.withOpacity(0.4)
                  : (isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder)),
          width: isUrgent || isPinned ? 1.5 : 1,
        ),
        boxShadow: [
          BoxShadow(
            color: (isUrgent ? AppTheme.red : const Color(0xFF0F172A))
                .withOpacity(isDark ? 0.2 : 0.04),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Top Header Row
          Container(
            padding: const EdgeInsets.fromLTRB(14, 12, 14, 8),
            decoration: BoxDecoration(
              color: isUrgent
                  ? AppTheme.red.withOpacity(isDark ? 0.15 : 0.06)
                  : (isPinned
                      ? AppTheme.primary.withOpacity(isDark ? 0.15 : 0.04)
                      : null),
              borderRadius:
                  const BorderRadius.vertical(top: Radius.circular(17)),
            ),
            child: Row(
              children: [
                // Icon Avatar
                Container(
                  width: 36,
                  height: 36,
                  decoration: BoxDecoration(
                    color: color.withOpacity(0.12),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Icon(
                    isUrgent
                        ? Icons.warning_amber_rounded
                        : (item['category'] == 'Exam Routine'
                            ? Icons.assignment_outlined
                            : Icons.campaign_outlined),
                    color: color,
                    size: 18,
                  ),
                ),
                const SizedBox(width: 10),

                // Author & Date
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Flexible(
                            child: Text(
                              item['author'] as String,
                              style: TextStyle(
                                fontSize: 12.5,
                                fontWeight: FontWeight.w800,
                                color: isDark ? Colors.white : AppTheme.dark,
                              ),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                          const SizedBox(width: 4),
                          const Icon(
                            Icons.verified_rounded,
                            size: 13,
                            color: AppTheme.primary,
                          ),
                        ],
                      ),
                      const SizedBox(height: 1),
                      Text(
                        item['nepaliDate'] as String,
                        style: const TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          color: AppTheme.darkSubtext,
                        ),
                      ),
                    ],
                  ),
                ),

                // Badges
                if (isUrgent)
                  Container(
                    margin: const EdgeInsets.only(right: 6),
                    padding:
                        const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: AppTheme.red,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Text(
                      'URGENT',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 9.5,
                        fontWeight: FontWeight.w900,
                        letterSpacing: 0.5,
                      ),
                    ),
                  )
                else if (isPinned)
                  Container(
                    margin: const EdgeInsets.only(right: 6),
                    padding:
                        const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                    decoration: BoxDecoration(
                      color: AppTheme.primary.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.push_pin_rounded,
                            size: 11, color: AppTheme.primary),
                        SizedBox(width: 3),
                        Text(
                          'PINNED',
                          style: TextStyle(
                            color: AppTheme.primary,
                            fontSize: 9.5,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ],
                    ),
                  ),

                // Popup menu
                PopupMenuButton<String>(
                  icon: const Icon(Icons.more_vert_rounded,
                      size: 18, color: AppTheme.darkSubtext),
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14)),
                  onSelected: (val) {
                    if (val == 'delete') {
                      setState(() => _announcements.remove(item));
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                          content: Text('Notice archived successfully'),
                          duration: Duration(seconds: 1),
                        ),
                      );
                    }
                  },
                  itemBuilder: (ctx) => [
                    const PopupMenuItem(
                      value: 'share',
                      child: Row(
                        children: [
                          Icon(Icons.share_outlined, size: 16),
                          SizedBox(width: 8),
                          Text('Share to Class WhatsApp',
                              style: TextStyle(fontSize: 12.5)),
                        ],
                      ),
                    ),
                    const PopupMenuItem(
                      value: 'delete',
                      child: Row(
                        children: [
                          Icon(Icons.archive_outlined,
                              size: 16, color: AppTheme.red),
                          SizedBox(width: 8),
                          Text('Archive Notice',
                              style: TextStyle(
                                  fontSize: 12.5, color: AppTheme.red)),
                        ],
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          // Title & Body
          Padding(
            padding: const EdgeInsets.fromLTRB(14, 8, 14, 10),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  item['title'] as String,
                  style: const TextStyle(
                    fontSize: 14.5,
                    fontWeight: FontWeight.w800,
                    letterSpacing: -0.2,
                    height: 1.3,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  item['body'] as String,
                  style: TextStyle(
                    fontSize: 12.5,
                    color: isDark ? Colors.white70 : AppTheme.darkSubtext,
                    height: 1.45,
                  ),
                ),
              ],
            ),
          ),

          // Attachment Chip if any
          if (hasAttachment)
            Padding(
              padding: const EdgeInsets.fromLTRB(14, 0, 14, 10),
              child: Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                decoration: BoxDecoration(
                  color: isDark
                      ? AppTheme.primary.withOpacity(0.12)
                      : const Color(0xFFEFF6FF),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(
                    color: AppTheme.primary.withOpacity(0.25),
                    width: 1,
                  ),
                ),
                child: Row(
                  children: [
                    const Icon(
                      Icons.picture_as_pdf_rounded,
                      size: 18,
                      color: AppTheme.red,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        item['attachment'] as String,
                        style: const TextStyle(
                          fontSize: 11.5,
                          fontWeight: FontWeight.w700,
                          color: AppTheme.primary,
                        ),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    const Icon(
                      Icons.download_rounded,
                      size: 16,
                      color: AppTheme.primary,
                    ),
                  ],
                ),
              ),
            ),

          // Bottom Target audience bar
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
            decoration: BoxDecoration(
              color: isDark ? Colors.white.withOpacity(0.02) : AppTheme.lightBg,
              borderRadius:
                  const BorderRadius.vertical(bottom: Radius.circular(17)),
              border: Border(
                top: BorderSide(
                  color: isDark
                      ? AppTheme.darkCardBorder
                      : AppTheme.cardBorder.withOpacity(0.5),
                  width: 0.8,
                ),
              ),
            ),
            child: Row(
              children: [
                const Icon(Icons.people_alt_outlined,
                    size: 13, color: AppTheme.darkSubtext),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    'Target: ${item['target']}',
                    style: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                      color: AppTheme.darkSubtext,
                    ),
                  ),
                ),
                Text(
                  '#${item['category']}',
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: AppTheme.primary,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  void _showComposeSheet(BuildContext context, bool isDark) {
    final titleCtrl = TextEditingController();
    final bodyCtrl = TextEditingController();
    String target = 'All Classes';
    String category = 'Academic';
    bool isUrgent = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: isDark ? AppTheme.darkCard : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (sheetCtx) => StatefulBuilder(
        builder: (ctx, setModal) => Padding(
          padding: EdgeInsets.fromLTRB(
            20,
            20,
            20,
            MediaQuery.of(ctx).viewInsets.bottom + 20,
          ),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Header
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Expanded(
                      child: Row(
                        children: [
                          Icon(Icons.campaign_rounded,
                              color: AppTheme.primary, size: 22),
                          SizedBox(width: 8),
                          Flexible(
                            child: Text(
                              'Publish Notice (नयाँ सूचना)',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w900,
                              ),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close_rounded, size: 20),
                      onPressed: () => Navigator.pop(sheetCtx),
                    ),
                  ],
                ),
                const SizedBox(height: 14),

                // Category & Target Row
                Row(
                  children: [
                    Expanded(
                      child: DropdownButtonFormField<String>(
                        value: category,
                        decoration: InputDecoration(
                          labelText: 'Category',
                          filled: true,
                          fillColor:
                              isDark ? AppTheme.darkBg : AppTheme.lightBg,
                          contentPadding: const EdgeInsets.symmetric(
                              horizontal: 12, vertical: 10),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: BorderSide.none,
                          ),
                        ),
                        items: ['Academic', 'Exam Routine', 'Administration']
                            .map((c) =>
                                DropdownMenuItem(value: c, child: Text(c)))
                            .toList(),
                        onChanged: (v) => setModal(() => category = v!),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: DropdownButtonFormField<String>(
                        value: target,
                        decoration: InputDecoration(
                          labelText: 'Send To',
                          filled: true,
                          fillColor:
                              isDark ? AppTheme.darkBg : AppTheme.lightBg,
                          contentPadding: const EdgeInsets.symmetric(
                              horizontal: 12, vertical: 10),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: BorderSide.none,
                          ),
                        ),
                        items: [
                          'All Classes',
                          'School Wide',
                          ...Provider.of<TeacherProvider>(context, listen: false)
                              .teacherClasses
                              .map((c) => c.section)
                        ]
                            .map((s) =>
                                DropdownMenuItem(value: s, child: Text(s)))
                            .toList(),
                        onChanged: (v) => setModal(() => target = v!),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),

                // Title field
                TextField(
                  controller: titleCtrl,
                  decoration: InputDecoration(
                    labelText: 'Notice Title / विषय',
                    hintText: 'e.g. Science Project Submission Deadline',
                    filled: true,
                    fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: BorderSide.none,
                    ),
                  ),
                ),
                const SizedBox(height: 12),

                // Body field
                TextField(
                  controller: bodyCtrl,
                  maxLines: 4,
                  decoration: InputDecoration(
                    labelText: 'Notice Body / विस्तृत विवरण',
                    hintText: 'Enter complete announcement message...',
                    filled: true,
                    fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: BorderSide.none,
                    ),
                  ),
                ),
                const SizedBox(height: 12),

                // Urgent switch
                SwitchListTile(
                  title: const Text(
                    'Mark as Urgent 🚨',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700),
                  ),
                  subtitle: const Text(
                    'Sends immediate push broadcast notification to parents and teachers',
                    style: TextStyle(fontSize: 11, color: AppTheme.darkSubtext),
                  ),
                  value: isUrgent,
                  activeColor: AppTheme.red,
                  contentPadding: EdgeInsets.zero,
                  onChanged: (v) => setModal(() => isUrgent = v),
                ),
                const SizedBox(height: 16),

                // Submit Button
                SizedBox(
                  width: double.infinity,
                  height: 48,
                  child: ElevatedButton(
                    onPressed: () {
                      if (titleCtrl.text.trim().isNotEmpty) {
                        final authorName = Provider.of<AuthProvider>(context, listen: false).teacherName;
                        setState(() {
                          _announcements.insert(0, {
                            'id': DateTime.now().millisecondsSinceEpoch.toString(),
                            'title': titleCtrl.text.trim(),
                            'body': bodyCtrl.text.trim().isNotEmpty
                                ? bodyCtrl.text.trim()
                                : 'Official notification for teachers and students.',
                            'nepaliDate': 'Today',
                            'author': authorName,
                            'designation': 'Teacher',
                            'target': target,
                            'category': category,
                            'pinned': false,
                            'isUrgent': isUrgent,
                            'attachment': null,
                            'color': isUrgent ? AppTheme.red : AppTheme.primary,
                          });
                        });
                        Navigator.pop(sheetCtx);
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            backgroundColor: AppTheme.green,
                            content: Text(
                              'Notice published to Nepal School Hub Notice Board ✅',
                            ),
                          ),
                        );
                      }
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppTheme.primary,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                    ),
                    child: const Text(
                      'Broadcast Notice Now 📢',
                      style: TextStyle(
                          fontSize: 14, fontWeight: FontWeight.w800),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
