{{--
  Rich question-text editor partial.
  Usage:
    @include('question-papers.partials.rich-editor', [
        'fieldId'    => 'questionText',   // hidden textarea name
        'editorId'   => 'richEditorDiv',  // editor mount point id
        'initValue'  => old('question_text', $question->question_text ?? ''),
    ])
  Requires KaTeX + MathLive to be loaded in the page head (see _editor_head partial).
--}}
@once
@push('head-scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16/dist/katex.min.css">
<script src="https://cdn.jsdelivr.net/npm/katex@0.16/dist/katex.min.js"></script>
<script type="module" src="https://unpkg.com/mathlive@0.100/dist/mathlive.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/mathlive@0.100/dist/mathlive-static.css">
<script src="https://cdn.jsdelivr.net/npm/@tiptap/starter-kit@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-underline@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-image@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-text-align@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-subscript@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-superscript@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-table@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-table-row@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-table-cell@2.4/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-table-header@2.4/dist/index.umd.min.js"></script>
<style>
.re-toolbar{display:flex;flex-wrap:wrap;gap:2px;padding:6px 8px;background:#f8fafc;border-bottom:1px solid #e2e8f0;border-radius:12px 12px 0 0;}
.re-toolbar button{min-width:28px;height:28px;border:none;background:transparent;border-radius:6px;font-size:12px;font-weight:600;color:#475569;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;padding:0 6px;transition:background .15s,color .15s;}
.re-toolbar button:hover,.re-toolbar button.active{background:#e0e7ff;color:#4f46e5;}
.re-toolbar .sep{width:1px;background:#e2e8f0;margin:2px 4px;align-self:stretch;}
.re-area{outline:none;min-height:100px;padding:10px 12px;font-size:14px;line-height:1.7;}
.re-area p{margin:0 0 4px;}
.re-area h1,.re-area h2,.re-area h3{font-weight:700;margin:4px 0 2px;}
.re-area ul,.re-area ol{padding-left:18px;}
.re-area table{border-collapse:collapse;width:100%;margin:6px 0;}
.re-area td,.re-area th{border:1px solid #cbd5e1;padding:4px 8px;}
.re-area .math-inline{background:#f0f4ff;border-radius:4px;padding:1px 5px;font-family:monospace;color:#3730a3;}
.re-math-modal-bg{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;display:flex;align-items:center;justify-content:center;padding:20px;}
.re-math-modal{background:#fff;border-radius:18px;padding:22px;width:100%;max-width:520px;box-shadow:0 16px 50px rgba(0,0,0,.2);}
</style>
@endpush
@endonce

@php $uid = $editorId ?? 're_editor_'.uniqid(); @endphp

<div class="border border-slate-200 rounded-xl overflow-hidden" id="re_wrap_{{ $uid }}">
  {{-- Toolbar --}}
  <div class="re-toolbar">
    <button type="button" onclick="RE.cmd('{{ $uid }}','bold')"       title="Bold"><b>B</b></button>
    <button type="button" onclick="RE.cmd('{{ $uid }}','italic')"     title="Italic"><i>I</i></button>
    <button type="button" onclick="RE.cmd('{{ $uid }}','underline')"  title="Underline" class="underline">U</button>
    <div class="sep"></div>
    <button type="button" onclick="RE.level('{{ $uid }}',1)"          title="H1">H1</button>
    <button type="button" onclick="RE.level('{{ $uid }}',2)"          title="H2">H2</button>
    <div class="sep"></div>
    <button type="button" onclick="RE.cmd('{{ $uid }}','bulletList')" title="Bullet list"><i class="bi bi-list-ul"></i></button>
    <button type="button" onclick="RE.cmd('{{ $uid }}','orderedList')"title="Ordered list"><i class="bi bi-list-ol"></i></button>
    <div class="sep"></div>
    <button type="button" onclick="RE.align('{{ $uid }}','left')"     title="Left"><i class="bi bi-text-left"></i></button>
    <button type="button" onclick="RE.align('{{ $uid }}','center')"   title="Center"><i class="bi bi-text-center"></i></button>
    <button type="button" onclick="RE.align('{{ $uid }}','right')"    title="Right"><i class="bi bi-text-right"></i></button>
    <div class="sep"></div>
    <button type="button" onclick="RE.cmd('{{ $uid }}','subscript')"   title="Subscript">X₂</button>
    <button type="button" onclick="RE.cmd('{{ $uid }}','superscript')" title="Superscript">X²</button>
    <div class="sep"></div>
    <button type="button" onclick="RE.table('{{ $uid }}')"            title="Table"><i class="bi bi-table"></i></button>
    <div class="sep"></div>
    <button type="button" onclick="RE.openMath('{{ $uid }}')"         title="Math equation" class="text-indigo-700 font-bold text-base">∑</button>
    <div class="sep"></div>
    <button type="button" onclick="RE.triggerImg('{{ $uid }}')"       title="Image"><i class="bi bi-image"></i></button>
    <input type="file" id="re_img_{{ $uid }}" accept="image/*" class="hidden" onchange="RE.imgUpload('{{ $uid }}',this)">
  </div>
  {{-- Editor --}}
  <div id="{{ $uid }}" class="re-area"></div>
</div>

{{-- Hidden textarea that holds the HTML value for form submit --}}
<textarea name="{{ $fieldId }}" id="{{ $fieldId }}" class="hidden">{{ $initValue ?? '' }}</textarea>

{{-- Math modal for this editor instance --}}
<div id="re_math_bg_{{ $uid }}" class="re-math-modal-bg hidden">
  <div class="re-math-modal">
    <div class="flex items-center justify-between mb-3">
      <h3 class="font-bold text-slate-800">Insert Math Equation</h3>
      <button type="button" onclick="RE.closeMath('{{ $uid }}')" class="text-slate-400 hover:text-slate-700"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="mb-3 flex flex-wrap gap-2">
      @foreach(['x^2','\\frac{a}{b}','\\sqrt{x}','\\int_a^b f(x)dx','\\sum_{i=1}^{n}','\\pi r^2','\\vec{v}','\\alpha + \\beta','\\begin{pmatrix}a&b\\\\c&d\\end{pmatrix}','\\lim_{x\\to\\infty}'] as $tpl)
      <button type="button" onclick="document.getElementById('re_mf_{{ $uid }}').value='{{ $tpl }}'"
              class="px-2 py-1 text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg font-mono transition">{{ $tpl }}</button>
      @endforeach
    </div>
    <label class="block text-xs text-slate-500 mb-1">LaTeX</label>
    <math-field id="re_mf_{{ $uid }}" class="w-full border border-slate-200 rounded-xl p-3 text-lg" style="min-height:55px;"></math-field>
    <p class="text-xs text-slate-400 mt-1">Type LaTeX or use the keyboard below the field.</p>
    <div class="flex gap-2 justify-end mt-4">
      <button type="button" onclick="RE.closeMath('{{ $uid }}')" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</button>
      <button type="button" onclick="RE.insertMath('{{ $uid }}')" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">Insert</button>
    </div>
  </div>
</div>

@once
@push('scripts')
<script>
const RE = (function(){
  const _eds={};
  function get(uid){return _eds[uid];}
  function init(uid,initHtml){
    const el=document.getElementById(uid);
    if(!el||_eds[uid])return;
    const{StarterKit}=window.Tiptap?.StarterKit||{};
    if(!window.Tiptap){setTimeout(()=>init(uid,initHtml),200);return;}
    const{Editor}=window.Tiptap.Core||{};
    if(!Editor){setTimeout(()=>init(uid,initHtml),200);return;}
    const ext=[
      window.Tiptap.StarterKit?.StarterKit?.configure()||window.Tiptap.StarterKit,
    ];
    try{
      _eds[uid]=new window.Tiptap.Core.Editor({
        element:el,
        extensions:[
          window.Tiptap.StarterKit,
          window.Tiptap.Extension?.Underline||window.TiptapUnderline,
          window.Tiptap.Extension?.Image||window.TiptapImage,
          window.Tiptap.Extension?.TextAlign?.configure({types:['heading','paragraph']})||window.TiptapTextAlign?.configure({types:['heading','paragraph']}),
          window.Tiptap.Extension?.Subscript||window.TiptapSubscript,
          window.Tiptap.Extension?.Superscript||window.TiptapSuperscript,
          window.Tiptap.Extension?.Table?.configure({resizable:false})||window.TiptapTable?.configure({resizable:false}),
          window.Tiptap.Extension?.TableRow||window.TiptapTableRow,
          window.Tiptap.Extension?.TableCell||window.TiptapTableCell,
          window.Tiptap.Extension?.TableHeader||window.TiptapTableHeader,
        ].filter(Boolean),
        content:initHtml||'',
        onUpdate:({editor})=>{
          const ta=document.getElementById(el.closest('[id^=re_wrap_]')?.parentElement?.querySelector('textarea')?.id||uid.replace('re_editor_',''));
          const taEl=document.querySelector(`textarea[id="${uid.replace(/[^a-z_]/gi,'')}_ta"]`)||
                     el.closest('form')?.querySelector(`textarea[name]`);
          // find hidden textarea by same base name
          const fieldName=el.closest('.border')?.nextElementSibling?.name;
          const fieldEl=el.closest('.border')?.nextElementSibling;
          if(fieldEl)fieldEl.value=editor.getHTML();
        }
      });
    }catch(e){console.warn('Tiptap init error:',e);}
  }
  function cmd(uid,c){
    const ed=_eds[uid];if(!ed)return;
    const chain=ed.chain().focus();
    if(c==='bulletList')chain.toggleBulletList().run();
    else if(c==='orderedList')chain.toggleOrderedList().run();
    else chain.toggleMark(c).run();
  }
  function level(uid,l){const ed=_eds[uid];if(ed)ed.chain().focus().toggleHeading({level:l}).run();}
  function align(uid,a){const ed=_eds[uid];if(ed)ed.chain().focus().setTextAlign(a).run();}
  function table(uid){const ed=_eds[uid];if(ed)ed.chain().focus().insertTable({rows:3,cols:3,withHeaderRow:true}).run();}
  function triggerImg(uid){document.getElementById('re_img_'+uid).click();}
  function imgUpload(uid,input){
    const file=input.files[0];if(!file)return;
    const r=new FileReader();r.onload=e=>{const ed=_eds[uid];if(ed)ed.chain().focus().setImage({src:e.target.result}).run();};
    r.readAsDataURL(file);
  }
  function openMath(uid){document.getElementById('re_math_bg_'+uid).classList.remove('hidden');}
  function closeMath(uid){document.getElementById('re_math_bg_'+uid).classList.add('hidden');}
  function insertMath(uid){
    const mf=document.getElementById('re_mf_'+uid);
    const latex=mf?.value;if(!latex)return;
    const ed=_eds[uid];
    if(ed)ed.chain().focus().insertContent(`<span class="math-inline">$${latex}$</span>&nbsp;`).run();
    closeMath(uid);
  }
  function getHTML(uid){return _eds[uid]?.getHTML()||'';}
  return{init,cmd,level,align,table,triggerImg,imgUpload,openMath,closeMath,insertMath,get,getHTML};
})();
// Init all editors on page load
document.addEventListener('DOMContentLoaded',()=>{
  document.querySelectorAll('.re-area').forEach(el=>{
    const wrap=el.closest('[id^=re_wrap_]');
    const ta=wrap?.nextElementSibling;
    RE.init(el.id, ta?.value||'');
    // Sync to textarea on form submit
    el.closest('form')?.addEventListener('submit',()=>{
      if(ta)ta.value=RE.getHTML(el.id);
    });
  });
});
</script>
@endpush
@endonce
