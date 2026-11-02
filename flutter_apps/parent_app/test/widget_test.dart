import 'package:flutter_test/flutter_test.dart';
import 'package:parent_app/main.dart';

void main() {
  testWidgets('ParentsApp smoke test', (WidgetTester tester) async {
    await tester.pumpWidget(const ParentsApp());
    expect(find.byType(ParentsApp), findsOneWidget);
    // Advance time past the splash screen timer (2.2 seconds)
    await tester.pump(const Duration(seconds: 3));
    await tester.pumpAndSettle();
  });
}
