import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../providers/api_provider.dart';
import '../widgets/common_widgets.dart';

class HealthScreen extends StatefulWidget {
  const HealthScreen({super.key});
  @override
  State<HealthScreen> createState() => _HealthScreenState();
}

class _HealthScreenState extends State<HealthScreen> {
  @override
  Widget build(BuildContext context) {
    final api = context.watch<ApiProvider>();
    final child = api.selectedChild;
    final health = api.healthProfile;

    return Scaffold(
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
              colors: const [Color(0xFF00B894), Color(0xFF00D2D3)],
              child: Column(
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Student Health Profile • ${child.name}',
                        style: const TextStyle(color: Colors.white70, fontSize: 12, fontWeight: FontWeight.w600),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(color: Colors.white.withOpacity(0.2), borderRadius: BorderRadius.circular(10)),
                        child: const Text('Fit for Sports ✅', style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w800)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: [
                      _HealthStat(label: 'Blood Type', value: health.bloodGroup),
                      _HealthStat(label: 'Height', value: health.height),
                      _HealthStat(label: 'Weight', value: health.weight),
                      _HealthStat(label: 'BMI Index', value: '19.2'),
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
                  const SizedBox(height: 10),
                  Row(
                    children: [
                      const Icon(Icons.phone_in_talk_rounded, color: AppTheme.red, size: 20),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          health.emergencyContact,
                          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      const Icon(Icons.local_hospital_rounded, color: AppTheme.blue, size: 20),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          health.doctorContact,
                          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Allergies & Special Care Section
            const SectionHeader(title: '⚠️ Allergies & Special Instructions'),
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
                          color: AppTheme.red.withOpacity(0.12),
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(color: AppTheme.red.withOpacity(0.3)),
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
                  const SizedBox(height: 10),
                  Text(
                    'Regular Medications: ${health.medications.join(", ")}',
                    style: const TextStyle(fontSize: 11, color: AppTheme.grey, fontWeight: FontWeight.w600),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            // Immunizations & Vaccinations List
            const SectionHeader(title: '💉 Immunization & Vaccination Record'),
            const SizedBox(height: 12),
            ...health.vaccinations.map((vac) {
              final isDone = vac.status.toLowerCase() == 'completed';
              return Container(
                margin: const EdgeInsets.only(bottom: 10),
                child: AppCard(
                  padding: const EdgeInsets.all(14),
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
                        label: isDone ? 'COMPLETED' : 'DUE SOON',
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
