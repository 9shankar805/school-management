import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../widgets/common_widgets.dart';
import '../providers/teacher_provider.dart';

class ResourcesScreen extends StatefulWidget {
  const ResourcesScreen({super.key});
  @override
  State<ResourcesScreen> createState() => _ResourcesScreenState();
}

class _ResourcesScreenState extends State<ResourcesScreen> {
  int _selectedClass = 0;
  String _filter = 'All';
  final _filters = ['All', 'PDF', 'Video', 'Doc', 'Link'];

  final List<Map<String, dynamic>> _resources = [
    {'title': 'Calculus Textbook Ch.5-8', 'type': 'PDF', 'size': '8.4 MB',
      'class': 'Grade 10 - A', 'date': 'Aug 10', 'colorHex': 0xFFE3F2FD, 'downloads': 28},
    {'title': 'Quadratic Formula Slides', 'type': 'PDF', 'size': '2.1 MB',
      'class': 'Grade 10 - A', 'date': 'Aug 12', 'colorHex': 0xFFE3F2FD, 'downloads': 30},
    {'title': 'Integration Video Lecture', 'type': 'Video', 'size': '245 MB',
      'class': 'Grade 11 - A', 'date': 'Aug 8', 'colorHex': 0xFFF3E5F5, 'downloads': 22},
    {'title': 'Statistics Reference Sheet', 'type': 'PDF', 'size': '1.2 MB',
      'class': 'Grade 12 - A', 'date': 'Aug 14', 'colorHex': 0xFFE8F5E9, 'downloads': 25},
    {'title': 'Practice Problems Set 3', 'type': 'Doc', 'size': '0.8 MB',
      'class': 'Grade 10 - B', 'date': 'Aug 15', 'colorHex': 0xFFE8EAF6, 'downloads': 18},
    {'title': 'Khan Academy - Calculus', 'type': 'Link', 'size': '',
      'class': 'Grade 11 - A', 'date': 'Aug 5', 'colorHex': 0xFFF3E5F5, 'downloads': 15},
    {'title': 'Geometry Proof Examples', 'type': 'PDF', 'size': '3.5 MB',
      'class': 'Grade 10 - B', 'date': 'Aug 11', 'colorHex': 0xFFE8EAF6, 'downloads': 20},
    {'title': 'Mid-Term Study Guide', 'type': 'Doc', 'size': '1.5 MB',
      'class': 'Grade 10 - A', 'date': 'Aug 16', 'colorHex': 0xFFE3F2FD, 'downloads': 32},
  ];

  List<Map<String, dynamic>> _getFiltered(List<TeacherClass> classes) {
    var list = _resources;
    if (_selectedClass > 0 && _selectedClass <= classes.length) {
      final targetSec = classes[_selectedClass - 1].section;
      list = list.where((r) => r['class'] == targetSec).toList();
    }
    if (_filter != 'All') {
      list = list.where((r) => r['type'] == _filter).toList();
    }
    return list;
  }

  IconData _iconForType(String type) {
    switch (type) {
      case 'PDF': return Icons.picture_as_pdf_outlined;
      case 'Video': return Icons.play_circle_outline_rounded;
      case 'Link': return Icons.link_rounded;
      default: return Icons.description_outlined;
    }
  }

  Color _colorForType(String type) {
    switch (type) {
      case 'PDF': return Colors.red;
      case 'Video': return AppTheme.blue;
      case 'Link': return AppTheme.teal;
      default: return AppTheme.orange;
    }
  }

  @override
  Widget build(BuildContext context) {
    final teacherProvider = Provider.of<TeacherProvider>(context);
    final classesList = teacherProvider.teacherClasses;
    final filtered = _getFiltered(classesList);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Resources'),
        leading: CircleIconButton(icon: Icons.arrow_back_ios_new,
            onTap: () => Navigator.pop(context), size: 38),
        leadingWidth: 60,
        actions: [
          CircleIconButton(icon: Icons.upload_outlined, onTap: () => _showUploadSheet(),
              bgColor: AppTheme.primary, iconColor: Colors.white, size: 38),
          const SizedBox(width: 16),
        ],
      ),
      body: Column(children: [
        // Class selector
        SizedBox(
          height: 44,
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 20),
            itemCount: classesList.length + 1,
            itemBuilder: (_, i) {
              final sel = i == _selectedClass;
              final label = i == 0 ? 'All Classes' : classesList[i - 1].section;
              return GestureDetector(
                onTap: () => setState(() => _selectedClass = i),
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  margin: const EdgeInsets.only(right: 8),
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                  decoration: BoxDecoration(
                    gradient: sel ? const LinearGradient(
                        colors: [AppTheme.primary, AppTheme.secondary]) : null,
                    color: sel ? null : (Theme.of(context).brightness == Brightness.dark
                        ? AppTheme.darkCard : Colors.white),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(label, style: TextStyle(fontSize: 12,
                      fontWeight: FontWeight.w600,
                      color: sel ? Colors.white : AppTheme.grey)),
                ),
              );
            },
          ),
        ),
        const SizedBox(height: 8),
        // Type filter
        SizedBox(
          height: 36,
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 20),
            itemCount: _filters.length,
            itemBuilder: (_, i) {
              final sel = _filters[i] == _filter;
              return GestureDetector(
                onTap: () => setState(() => _filter = _filters[i]),
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  margin: const EdgeInsets.only(right: 8),
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                  decoration: BoxDecoration(
                    color: sel ? AppTheme.primary : Colors.white,
                    borderRadius: BorderRadius.circular(20),
                    boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05),
                        blurRadius: 4, offset: const Offset(0, 1))],
                  ),
                  child: Text(_filters[i], style: TextStyle(fontSize: 12,
                      fontWeight: FontWeight.w600,
                      color: sel ? Colors.white : AppTheme.grey)),
                ),
              );
            },
          ),
        ),
        const SizedBox(height: 12),
        Expanded(
          child: _filtered.isEmpty
              ? const Center(child: Column(
                  mainAxisAlignment: MainAxisAlignment.center, children: [
                  Icon(Icons.folder_open_outlined, size: 64, color: AppTheme.grey),
                  SizedBox(height: 12),
                  Text('No resources found', style: TextStyle(color: AppTheme.grey)),
                ]))
              : ListView.builder(
                  padding: const EdgeInsets.fromLTRB(20, 0, 20, 24),
                  itemCount: _filtered.length,
                  itemBuilder: (_, i) {
                    final r = _filtered[i];
                    final typeColor = _colorForType(r['type'] as String);
                    return Container(
                      margin: const EdgeInsets.only(bottom: 10),
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(16),
                        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.04),
                            blurRadius: 8, offset: const Offset(0, 2))],
                      ),
                      child: Row(children: [
                        Container(
                          width: 48, height: 48,
                          decoration: BoxDecoration(
                              color: typeColor.withOpacity(0.1),
                              borderRadius: BorderRadius.circular(14)),
                          child: Icon(_iconForType(r['type'] as String),
                              color: typeColor, size: 24),
                        ),
                        const SizedBox(width: 12),
                        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                          Text(r['title'] as String,
                              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
                          const SizedBox(height: 3),
                          Row(children: [
                            PillBadge(label: r['type'] as String,
                                color: typeColor.withOpacity(0.1), textColor: typeColor),
                            const SizedBox(width: 6),
                            if ((r['size'] as String).isNotEmpty)
                              Text(r['size'] as String,
                                  style: const TextStyle(fontSize: 10, color: AppTheme.grey)),
                          ]),
                          const SizedBox(height: 3),
                          Text('${r['class']} • ${r['date']}',
                              style: const TextStyle(fontSize: 10, color: AppTheme.grey)),
                        ])),
                        Column(crossAxisAlignment: CrossAxisAlignment.end, children: [
                          IconButton(
                            icon: const Icon(Icons.download_outlined,
                                color: AppTheme.primary, size: 20),
                            onPressed: () {},
                            padding: EdgeInsets.zero,
                            constraints: const BoxConstraints(),
                          ),
                          const SizedBox(height: 4),
                          Text('${r['downloads']}',
                              style: const TextStyle(fontSize: 10, color: AppTheme.grey)),
                        ]),
                      ]),
                    ).animate().fadeIn(delay: (i * 50).ms);
                  },
                ),
        ),
      ]),
    );
  }

  void _showUploadSheet() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (_) => Padding(
        padding: const EdgeInsets.all(24),
        child: Column(mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start, children: [
          const Text('Upload Resource', style: TextStyle(
              fontSize: 18, fontWeight: FontWeight.w800, color: AppTheme.dark)),
          const SizedBox(height: 20),
          Row(children: [
            _UploadOption(icon: Icons.picture_as_pdf_outlined,
                label: 'PDF', color: Colors.red),
            const SizedBox(width: 12),
            _UploadOption(icon: Icons.videocam_outlined,
                label: 'Video', color: AppTheme.blue),
            const SizedBox(width: 12),
            _UploadOption(icon: Icons.description_outlined,
                label: 'Document', color: AppTheme.orange),
            const SizedBox(width: 12),
            _UploadOption(icon: Icons.link_rounded,
                label: 'Link', color: AppTheme.teal),
          ]),
          const SizedBox(height: 20),
          SizedBox(width: double.infinity, height: 50,
            child: ElevatedButton(
              onPressed: () => Navigator.pop(context),
              style: ElevatedButton.styleFrom(backgroundColor: AppTheme.primary,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14))),
              child: const Text('Choose File', style: TextStyle(fontWeight: FontWeight.w700)),
            ),
          ),
        ]),
      ),
    );
  }
}

class _UploadOption extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;
  const _UploadOption({required this.icon, required this.label, required this.color});

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 14),
        decoration: BoxDecoration(
            color: color.withOpacity(0.1),
            borderRadius: BorderRadius.circular(14)),
        child: Column(children: [
          Icon(icon, color: color, size: 24),
          const SizedBox(height: 4),
          Text(label, style: TextStyle(fontSize: 10,
              fontWeight: FontWeight.w700, color: color)),
        ]),
      ),
    );
  }
}
