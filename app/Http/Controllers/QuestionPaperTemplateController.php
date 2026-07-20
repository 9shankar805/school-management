<?php

namespace App\Http\Controllers;

use App\Models\QuestionPaperTemplate;
use Illuminate\Http\Request;

class QuestionPaperTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware('can:create exams')->except(['index', 'show']);
    }

    public function index()
    {
        $templates = QuestionPaperTemplate::with('creator')
            ->where('is_active', true)
            ->latest()
            ->get();

        return view('question-papers.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('question-papers.templates.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string|max:500',
            'school_name'        => 'nullable|string|max:255',
            'school_address'     => 'nullable|string|max:500',
            'header_html'        => 'nullable|string',
            'instructions_html'  => 'nullable|string',
            'footer_html'        => 'nullable|string',
            'signature_name'     => 'nullable|string|max:255',
            'signature_title'    => 'nullable|string|max:255',
            'paper_size'         => 'required|in:A4,Letter',
            'orientation'        => 'required|in:portrait,landscape',
            'show_watermark'     => 'boolean',
            'watermark_text'     => 'nullable|string|max:100',
        ]);

        QuestionPaperTemplate::create(array_merge($data, [
            'created_by'     => auth()->id(),
            'is_active'      => true,
            'show_watermark' => $request->boolean('show_watermark'),
        ]));

        return redirect()->route('question-paper-templates.index')
            ->with('status', 'Template "' . $data['name'] . '" created.');
    }

    public function show(int $id)
    {
        $template = QuestionPaperTemplate::with('papers')->findOrFail($id);
        return view('question-papers.templates.show', compact('template'));
    }

    public function edit(int $id)
    {
        $template = QuestionPaperTemplate::findOrFail($id);
        return view('question-papers.templates.edit', compact('template'));
    }

    public function update(Request $request, int $id)
    {
        $template = QuestionPaperTemplate::findOrFail($id);

        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'description'       => 'nullable|string|max:500',
            'school_name'       => 'nullable|string|max:255',
            'school_address'    => 'nullable|string|max:500',
            'header_html'       => 'nullable|string',
            'instructions_html' => 'nullable|string',
            'footer_html'       => 'nullable|string',
            'signature_name'    => 'nullable|string|max:255',
            'signature_title'   => 'nullable|string|max:255',
            'paper_size'        => 'required|in:A4,Letter',
            'orientation'       => 'required|in:portrait,landscape',
            'show_watermark'    => 'boolean',
            'watermark_text'    => 'nullable|string|max:100',
            'is_active'         => 'boolean',
        ]);

        $template->update(array_merge($data, [
            'show_watermark' => $request->boolean('show_watermark'),
            'is_active'      => $request->boolean('is_active', true),
        ]));

        return redirect()->route('question-paper-templates.index')
            ->with('status', 'Template updated.');
    }

    public function destroy(int $id)
    {
        QuestionPaperTemplate::findOrFail($id)->delete();
        return back()->with('status', 'Template deleted.');
    }
}
