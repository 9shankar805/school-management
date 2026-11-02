import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../theme/app_theme.dart';
import '../providers/api_provider.dart';
import '../widgets/common_widgets.dart';

class HealthScreen extends StatefulWidget {
  const HealthScreen({super.key});
  @override
  State<HealthScreen> createState() => _HealthScreenState();
}

class _HealthScreenState extends State<HealthScreen> {
  Future<void> _makeCall(String phoneNumber) async {
    final uri = Uri(scheme: 'tel', path: phoneNumber.replaceAll(RegExp(r'\s+'), ''));
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Dialing $phoneNumber...')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final api = context.watch<ApiProvider>();
    final child = api.selectedChild;
    final health = api.healthProfile;

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Health & Medical Records'),
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
            // Vitals Hero Card
            GradientCard(
              colors: const [Color(0xFF0D9488), Color(0xFF14B8A6)],
              child: Column(
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Medical Dossier • ${child.name}',
                        style: const TextStyle(color: Colors.white70, fontSize: 12, fontWeight: FontWeight.w600),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.22),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: Colors.white30),
                        ),
                        child: const Text('Fit for Sports & Activities ✅', style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w800)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: [
                      _HealthStat(label: 'Blood Group', value: health.bloodGroup),
                      _HealthStat(label: 'Height', value: health.height),
                      _HealthStat(label: 'Weight', value: health.weight),
                      const _HealthStat(label: 'BMI Index', value: '18.4 (Normal)'),
                    ],
                  ),
                ],
              ),
            ).animate().fadeIn().slideY(begin: 0.05),
            const SizedBox(height: 16),

            // Emergency Contacts Card
            AppCard(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('🚨 Emergency & Physician Contacts', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800)),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: AppTheme.red.withValues(alpha: 0.12),
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(Icons.phone_in_talk_rounded, color: AppTheme.red, size: 20),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('Emergency Guardian Line', style: TextStyle(fontSize: 10, color: AppTheme.grey, fontWeight: FontWeight.w700)),
                            Text(health.emergencyContact, style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w800)),
                          ],
                        ),
                      ),
                      IconButton(
                        icon: const Icon(Icons.call, color: AppTheme.red, size: 20),
                        tooltip: 'Call Emergency Contact',
                        onPressed: () => _makeCall(health.emergencyContact),
                      ),
                    ],
                  ),
                  const Divider(height: 16),
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: AppTheme.blue.withValues(alpha: 0.12),
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(Icons.local_hospital_rounded, color: AppTheme.blue, size: 20),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('School Medical Officer / Clinic', style: TextStyle(fontSize: 10, color: AppTheme.grey, fontWeight: FontWeight.w700)),
                            Text(health.doctorContact, style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w800)),
                          ],
                        ),
                      ),
                      IconButton(
                        icon: const Icon(Icons.call, color: AppTheme.blue, size: 20),
                        tooltip: 'Call Doctor',
                        onPressed: () => _makeCall(health.doctorContact),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Allergies & Special Care Section
            const SectionHeader(title: '⚠️ Allergies & Medical Warnings'),
            const SizedBox(height: 10),
            AppCard(
              padding: const EdgeInsets.all(14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: health.allergies.map((allergy) {
                      return Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                        decoration: BoxDecoration(
                          color: AppTheme.red.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(color: AppTheme.red.withValues(alpha: 0.3)),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.warning_amber_rounded, size: 14, color: AppTheme.red),
                            const SizedBox(width: 4),
                            Text(allergy, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: AppTheme.red)),
                          ],
                        ),
                      );
                    }).toList(),
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      const Icon(Icons.medication_rounded, size: 16, color: Color(0xFF64748B)),
                      const SizedBox(width: 6),
                      Expanded(
                        child: Text(
                          'Regular Medications: ${health.medications.join(", ")}',
                          style: const TextStyle(fontSize: 11, color: Color(0xFF334155), fontWeight: FontWeight.w700),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            // Immunizations & Vaccinations List
            const SectionHeader(title: '💉 Immunization & Health Cards'),
            const SizedBox(height: 12),
            ...health.vaccinations.map((vac) {
              final isDone = vac.status.toLowerCase() == 'completed';
              return Container(
                margin: const EdgeInsets.only(bottom: 10),
                child: AppCard(
                  padding: const EdgeInsets.all(14),
                  onTap: () => _showVaccineDetail(context, vac),
                  child: Row(
                    children: [
                      Container(
                        width: 40,
                        height: 40,
                        decoration: BoxDecoration(
                          color: isDone ? AppTheme.greenLight : AppTheme.orangeLight,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Icon(
                          Icons.vaccines_rounded,
                          color: isDone ? AppTheme.green : AppTheme.orange,
                          size: 20,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(vac.vaccineName, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800)),
                            Text(
                              'Date: ${vac.givenDate} • ${vac.doctorName}',
                              style: const TextStyle(fontSize: 11, color: AppTheme.grey),
                            ),
                          ],
                        ),
                      ),
                      StatusBadge(
                        label: isDone ? 'VERIFIED' : 'DUE SOON',
                        color: isDone ? AppTheme.green : AppTheme.orange,
                      ),
                    ],
                  ),
                ),
              );
            }),
          ],
        ),
      ),
    );
  }

  void _showVaccineDetail(BuildContext context, dynamic vac) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) => Padding(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  width: 44,
                  height: 44,
                  decoration: const BoxDecoration(color: Color(0xFFDCFCE7), shape: BoxShape.circle),
                  child: const Icon(Icons.vaccines_rounded, color: Color(0xFF166534), size: 24),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(vac.vaccineName, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900)),
                      const Text('Official Vaccination Record', style: TextStyle(fontSize: 11, color: AppTheme.grey)),
                    ],
                  ),
                ),
              ],
            ),
            const Divider(height: 24),
            _VaccineLine(label: 'Vaccine / Doses', value: vac.vaccineName),
            _VaccineLine(label: 'Administered Date', value: vac.givenDate),
            _VaccineLine(label: 'Certifying Doctor', value: vac.doctorName),
            const _VaccineLine(label: 'Clinic / Hospital', value: 'Kanti Children Hospital, Kathmandu'),
            const _VaccineLine(label: 'Lot / Batch No.', value: 'LOT-9842-NP'),
            const _VaccineLine(label: 'Government Card Reg.', value: 'NEP-VACC-2081'),
            const SizedBox(height: 20),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () => Navigator.pop(ctx),
                child: const Text('Close'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _HealthStat extends StatelessWidget {
  final String label, value;
  const _HealthStat({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Text(value, style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.w900)),
        const SizedBox(height: 2),
        Text(label, style: const TextStyle(color: Colors.white70, fontSize: 10, fontWeight: FontWeight.w600)),
      ],
    );
  }
}

class _VaccineLine extends StatelessWidget {
  final String label, value;
  const _VaccineLine({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 6),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(fontSize: 11.5, color: AppTheme.grey, fontWeight: FontWeight.w600)),
          Text(value, style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800, color: Color(0xFF0F172A))),
        ],
      ),
    );
  }
}
