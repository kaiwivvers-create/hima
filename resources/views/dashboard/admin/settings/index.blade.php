@extends('dashboard.layout')

@section('title', 'App Settings')
@section('page_title', 'App Settings')

@section('content')
<style>
    @media (max-width: 980px) {
        .app-settings-main,
        .app-settings-side {
            grid-column: span 12 !important;
        }
    }
</style>

<div class="grid" style="margin-bottom:.9rem;">
    <section class="card app-settings-main" style="grid-column:span 8;">
        <h2 style="margin:.1rem 0 .5rem;font-size:1.1rem;">Branding</h2>
        <p class="muted" style="margin:0 0 .85rem;">App name and logo for dashboard + front page.</p>

        <div class="card" style="margin:0 0 .8rem; background:#fff9df;">
            <p class="muted" style="margin:0 0 .45rem; font-weight:700;">Live Preview</p>
            <div style="display:flex; align-items:center; gap:.65rem;">
                @if ($appLogoPath)
                    <img src="{{ asset('storage/'.$appLogoPath) }}" alt="Current app logo" style="width:54px;height:54px;border-radius:12px;border:1px solid var(--line);object-fit:cover;">
                @else
                    <div style="width:54px;height:54px;border-radius:12px;border:1px solid var(--line);display:grid;place-items:center;font-weight:800;background:#fff3bf;">
                        {{ strtoupper(substr($appName, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <p style="margin:0;font-size:1.05rem;font-weight:800;">{{ $appName }}</p>
                    <p class="muted" style="margin:.1rem 0 0;">Sidebar and homepage title/logo.</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('dashboard.admin.settings.branding.update', ['lang' => app()->getLocale()]) }}" enctype="multipart/form-data">
            @csrf
            <div class="field" style="max-width:520px;">
                <label for="app_name">App Name</label>
                <input id="app_name" name="app_name" type="text" value="{{ old('app_name', $appName) }}" required>
            </div>
            <div class="field" style="max-width:520px;">
                <label for="app_logo">Logo (Square Recommended)</label>
                <input id="app_logo" name="app_logo" type="file" accept="image/*">
                <p class="muted" style="margin:.25rem 0 0; font-size:.86rem;">PNG/JPG up to 2MB.</p>
            </div>
            <div class="actions" style="margin-top:.8rem;">
                <button type="submit" class="btn">Save Branding</button>
            </div>
        </form>
    </section>

    <section class="card app-settings-side" style="grid-column:span 4;">
        <h2 style="margin:.1rem 0 .5rem;font-size:1.05rem;">Branding Iterations</h2>
        <p class="muted" style="margin:0 0 .8rem;">Most recent 3 branding changes.</p>
        <div style="display:flex; flex-direction:column; gap:.6rem;">
            @forelse ($recentBrandingVersions as $version)
                <article class="card" style="margin:0; background:#fff9df; border-style:dashed;">
                    <div style="display:flex; align-items:center; gap:.55rem;">
                        @if ($version->app_logo_path)
                            <img src="{{ asset('storage/'.$version->app_logo_path) }}" alt="Version logo" style="width:34px;height:34px;border-radius:9px;border:1px solid var(--line);object-fit:cover;">
                        @else
                            <div style="width:34px;height:34px;border-radius:9px;border:1px solid var(--line);display:grid;place-items:center;font-size:.78rem;font-weight:800;background:#fff3bf;">
                                {{ strtoupper(substr($version->app_name, 0, 1)) }}
                            </div>
                        @endif
                        <div style="min-width:0;">
                            <p style="margin:0; font-weight:700; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $version->app_name }}</p>
                            <p class="muted" style="margin:.05rem 0 0; font-size:.82rem;">{{ $version->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('dashboard.admin.settings.versions.apply-branding', ['version' => $version->id, 'lang' => app()->getLocale()]) }}" style="margin-top:.5rem;">
                        @csrf
                        <button type="submit" class="btn-outline">Apply Branding</button>
                    </form>
                </article>
            @empty
                <p class="muted" style="margin:0;">No branding iterations yet.</p>
            @endforelse
        </div>
    </section>
</div>

<div class="grid">
    <section class="card app-settings-main" style="grid-column:span 8;">
        <h2 style="margin:.1rem 0 .5rem;font-size:1.1rem;">Front Page Content</h2>
        <p class="muted" style="margin:0 0 .85rem;">Text content shown on the public welcome page.</p>

        <form method="POST" action="{{ route('dashboard.admin.settings.content.update', ['lang' => app()->getLocale()]) }}">
            @csrf
            @php
                $languageLabels = ['en' => 'English', 'id' => 'Bahasa Indonesia', 'zh' => '中文'];
            @endphp
            @foreach ($languageLabels as $langKey => $langLabel)
                <div class="card" style="margin:0 0 .8rem; background:#fff9df;">
                    <h3 style="margin:.1rem 0 .6rem;font-size:1rem;">{{ $langLabel }}</h3>

                    <div class="field">
                        <label for="text_{{ $langKey }}_welcome_nav_dashboard">Top Nav: Dashboard</label>
                        <input id="text_{{ $langKey }}_welcome_nav_dashboard" name="text[{{ $langKey }}][welcome_nav_dashboard]" type="text" value="{{ old('text.'.$langKey.'.welcome_nav_dashboard', $textSettings[$langKey]['welcome_nav_dashboard'] ?? '') }}">
                    </div>
                    <div class="field">
                        <label for="text_{{ $langKey }}_welcome_nav_login">Top Nav: Log In</label>
                        <input id="text_{{ $langKey }}_welcome_nav_login" name="text[{{ $langKey }}][welcome_nav_login]" type="text" value="{{ old('text.'.$langKey.'.welcome_nav_login', $textSettings[$langKey]['welcome_nav_login'] ?? '') }}">
                    </div>
                    <div class="field">
                        <label for="text_{{ $langKey }}_welcome_nav_register">Top Nav: Register</label>
                        <input id="text_{{ $langKey }}_welcome_nav_register" name="text[{{ $langKey }}][welcome_nav_register]" type="text" value="{{ old('text.'.$langKey.'.welcome_nav_register', $textSettings[$langKey]['welcome_nav_register'] ?? '') }}">
                    </div>
                    <div class="field">
                        <label for="text_{{ $langKey }}_welcome_hero_description">Hero Subtitle</label>
                        <textarea id="text_{{ $langKey }}_welcome_hero_description" name="text[{{ $langKey }}][welcome_hero_description]" rows="2">{{ old('text.'.$langKey.'.welcome_hero_description', $textSettings[$langKey]['welcome_hero_description'] ?? '') }}</textarea>
                    </div>
                    <div class="field">
                        <label for="text_{{ $langKey }}_welcome_section_1_title">Section 1 Title</label>
                        <input id="text_{{ $langKey }}_welcome_section_1_title" name="text[{{ $langKey }}][welcome_section_1_title]" type="text" value="{{ old('text.'.$langKey.'.welcome_section_1_title', $textSettings[$langKey]['welcome_section_1_title'] ?? '') }}">
                    </div>
                    <div class="field">
                        <label for="text_{{ $langKey }}_welcome_section_1_body">Section 1 Body</label>
                        <textarea id="text_{{ $langKey }}_welcome_section_1_body" name="text[{{ $langKey }}][welcome_section_1_body]" rows="5">{{ old('text.'.$langKey.'.welcome_section_1_body', $textSettings[$langKey]['welcome_section_1_body'] ?? '') }}</textarea>
                    </div>
                    <div class="field">
                        <label for="text_{{ $langKey }}_welcome_section_2_title">Section 2 Title</label>
                        <input id="text_{{ $langKey }}_welcome_section_2_title" name="text[{{ $langKey }}][welcome_section_2_title]" type="text" value="{{ old('text.'.$langKey.'.welcome_section_2_title', $textSettings[$langKey]['welcome_section_2_title'] ?? '') }}">
                    </div>
                    <div class="field">
                        <label for="text_{{ $langKey }}_welcome_section_2_body">Section 2 Body</label>
                        <textarea id="text_{{ $langKey }}_welcome_section_2_body" name="text[{{ $langKey }}][welcome_section_2_body]" rows="4">{{ old('text.'.$langKey.'.welcome_section_2_body', $textSettings[$langKey]['welcome_section_2_body'] ?? '') }}</textarea>
                    </div>
                    <div class="field">
                        <label for="text_{{ $langKey }}_welcome_section_3_title">Section 3 Title</label>
                        <input id="text_{{ $langKey }}_welcome_section_3_title" name="text[{{ $langKey }}][welcome_section_3_title]" type="text" value="{{ old('text.'.$langKey.'.welcome_section_3_title', $textSettings[$langKey]['welcome_section_3_title'] ?? '') }}">
                    </div>
                    <div class="field">
                        <label for="text_{{ $langKey }}_welcome_section_3_body">Section 3 Body</label>
                        <textarea id="text_{{ $langKey }}_welcome_section_3_body" name="text[{{ $langKey }}][welcome_section_3_body]" rows="4">{{ old('text.'.$langKey.'.welcome_section_3_body', $textSettings[$langKey]['welcome_section_3_body'] ?? '') }}</textarea>
                    </div>
                    <div class="field">
                        <label for="text_{{ $langKey }}_welcome_person_title">Person Section Title</label>
                        <input id="text_{{ $langKey }}_welcome_person_title" name="text[{{ $langKey }}][welcome_person_title]" type="text" value="{{ old('text.'.$langKey.'.welcome_person_title', $textSettings[$langKey]['welcome_person_title'] ?? '') }}">
                    </div>
                    <div class="field">
                        <label for="text_{{ $langKey }}_welcome_person_body">Person Section Body</label>
                        <textarea id="text_{{ $langKey }}_welcome_person_body" name="text[{{ $langKey }}][welcome_person_body]" rows="3">{{ old('text.'.$langKey.'.welcome_person_body', $textSettings[$langKey]['welcome_person_body'] ?? '') }}</textarea>
                    </div>
                    <div class="field">
                        <label for="text_{{ $langKey }}_welcome_map_title">Map Section Title</label>
                        <input id="text_{{ $langKey }}_welcome_map_title" name="text[{{ $langKey }}][welcome_map_title]" type="text" value="{{ old('text.'.$langKey.'.welcome_map_title', $textSettings[$langKey]['welcome_map_title'] ?? '') }}">
                    </div>
                    <div class="field">
                        <label for="text_{{ $langKey }}_welcome_map_body">Map Section Body</label>
                        <textarea id="text_{{ $langKey }}_welcome_map_body" name="text[{{ $langKey }}][welcome_map_body]" rows="3">{{ old('text.'.$langKey.'.welcome_map_body', $textSettings[$langKey]['welcome_map_body'] ?? '') }}</textarea>
                    </div>
                    <hr style="border:none;border-top:1px dashed rgba(42,33,0,.2);margin:.8rem 0;">
                    <div class="field">
                        <label for="text_{{ $langKey }}_payment_proof_rekening_text">Payment Proof Text</label>
                        <input id="text_{{ $langKey }}_payment_proof_rekening_text" name="text[{{ $langKey }}][payment_proof_rekening_text]" type="text" value="{{ old('text.'.$langKey.'.payment_proof_rekening_text', $textSettings[$langKey]['payment_proof_rekening_text'] ?? '') }}" placeholder="Pay to this nomor rekening: 1234567890">
                    </div>
                    <div class="field">
                        <label for="text_{{ $langKey }}_welcome_contact_email_href">Home Contact: Email Link</label>
                        <input id="text_{{ $langKey }}_welcome_contact_email_href" name="text[{{ $langKey }}][welcome_contact_email_href]" type="text" value="{{ old('text.'.$langKey.'.welcome_contact_email_href', $textSettings[$langKey]['welcome_contact_email_href'] ?? '') }}" placeholder="mailto:your@email.com">
                    </div>
                    <div class="field">
                        <label for="text_{{ $langKey }}_welcome_contact_email_label">Home Contact: Email Text</label>
                        <input id="text_{{ $langKey }}_welcome_contact_email_label" name="text[{{ $langKey }}][welcome_contact_email_label]" type="text" value="{{ old('text.'.$langKey.'.welcome_contact_email_label', $textSettings[$langKey]['welcome_contact_email_label'] ?? '') }}" placeholder="hello@example.com">
                    </div>
                    <div class="field">
                        <label for="text_{{ $langKey }}_welcome_contact_whatsapp_href">Home Contact: WhatsApp Link</label>
                        <input id="text_{{ $langKey }}_welcome_contact_whatsapp_href" name="text[{{ $langKey }}][welcome_contact_whatsapp_href]" type="text" value="{{ old('text.'.$langKey.'.welcome_contact_whatsapp_href', $textSettings[$langKey]['welcome_contact_whatsapp_href'] ?? '') }}" placeholder="https://wa.me/6281234567890">
                    </div>
                    <div class="field">
                        <label for="text_{{ $langKey }}_welcome_contact_whatsapp_label">Home Contact: WhatsApp Text</label>
                        <input id="text_{{ $langKey }}_welcome_contact_whatsapp_label" name="text[{{ $langKey }}][welcome_contact_whatsapp_label]" type="text" value="{{ old('text.'.$langKey.'.welcome_contact_whatsapp_label', $textSettings[$langKey]['welcome_contact_whatsapp_label'] ?? '') }}" placeholder="+62 812-3456-7890">
                    </div>
                    <div class="field">
                        <label for="text_{{ $langKey }}_welcome_contact_instagram_href">Home Contact: Instagram Link</label>
                        <input id="text_{{ $langKey }}_welcome_contact_instagram_href" name="text[{{ $langKey }}][welcome_contact_instagram_href]" type="text" value="{{ old('text.'.$langKey.'.welcome_contact_instagram_href', $textSettings[$langKey]['welcome_contact_instagram_href'] ?? '') }}" placeholder="https://instagram.com/youraccount">
                    </div>
                    <div class="field">
                        <label for="text_{{ $langKey }}_welcome_contact_instagram_label">Home Contact: Instagram Text</label>
                        <input id="text_{{ $langKey }}_welcome_contact_instagram_label" name="text[{{ $langKey }}][welcome_contact_instagram_label]" type="text" value="{{ old('text.'.$langKey.'.welcome_contact_instagram_label', $textSettings[$langKey]['welcome_contact_instagram_label'] ?? '') }}" placeholder="@yourusername">
                    </div>
                </div>
            @endforeach
            <div class="actions" style="margin-top:.8rem;">
                <button type="submit" class="btn">Save Front Page Content</button>
            </div>
        </form>
    </section>

    <section class="card app-settings-side" style="grid-column:span 4;">
        <h2 style="margin:.1rem 0 .5rem;font-size:1.05rem;">Content Iterations</h2>
        <p class="muted" style="margin:0 0 .8rem;">Most recent 3 front page content changes.</p>
        <div style="display:flex; flex-direction:column; gap:.6rem;">
            @forelse ($recentContentVersions as $version)
                <article class="card" style="margin:0; background:#fff9df; border-style:dashed;">
                    <p style="margin:0; font-weight:700;">Content Snapshot</p>
                    <p class="muted" style="margin:.08rem 0 0; font-size:.82rem;">{{ $version->created_at->format('Y-m-d H:i') }}</p>
                    <form method="POST" action="{{ route('dashboard.admin.settings.versions.apply-content', ['version' => $version->id, 'lang' => app()->getLocale()]) }}" style="margin-top:.5rem;">
                        @csrf
                        <button type="submit" class="btn-outline">Apply Content</button>
                    </form>
                </article>
            @empty
                <p class="muted" style="margin:0;">No content iterations yet.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
