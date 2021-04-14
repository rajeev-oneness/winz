<div class="tile">
    <form action="{{ route('admin.settings.update') }}" method="POST" role="form" id="faq-seo-form">
        @csrf
        <h3 class="tile-title">Faq Page Seo</h3>
        <hr>
        <div class="tile-body">
            <div class="form-group">
                <label class="control-label" for="faq_meta_title">India Meta Title</label>
                <input
                    class="form-control"
                    type="text"
                    placeholder="Enter Meta Title"
                    id="indmeta_title"
                    name="faq_meta_title"
                    value="{{ $setting::get('faq_meta_title') }}"
                />
            </div>
            <div class="form-group">
                <label class="control-label" for="faq_meta_keywords">India Meta Keywords</label>
                <input
                    class="form-control"
                    type="text"
                    placeholder="Enter Meta Keywords"
                    id="meta_keywords"
                    name="faq_meta_keywords"
                    value="{{ $setting::get('faq_meta_keywords') }}"
                />
            </div>
            
            <div class="form-group">
                <label class="control-label" for="faq_meta_description">India Meta Description</label>
                <textarea class="form-control ckeditor" name="faq_meta_description" id="faq_meta_description">{{ $setting::get('faq_meta_description') }}</textarea>
            </div>
            
        </div>
    </form>
</div>