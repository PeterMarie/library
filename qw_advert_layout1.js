function show_qw_advert(ad_index){
    let ad_html;
    const adBox = document.createElement("div");
    
    switch (ad_index) {
        case 1:
            ad_html = `<div class="qw-ad-header">
                    <strong>🎓 QuillWizards Can Help!</strong>
                    <button id="qw-close-btn" aria-label="Close">&times;</button>
                </div>
                <div class="qw-ad-body">
                    <p><strong>Need help with your studies?</strong> We offer:</p>
                    <ul>
                        <li>✅ Dissertations, Essays & Reports</li>
                        <li>✅ Academic Posters & Presentations</li>
                        <li>✅ Application Essays & Statements of Purpose</li>
                        <li>✅ Excel, PowerPoint, SPSS & Decision Tools</li>
                        <li>✅ Human-edited & plagiarism-free writing</li>
                    </ul>
                    <p><em>Trusted by students worldwide 🌍</em></p>
                    <a href="https://quillwizards.com" target="_blank" class="qw-cta">Visit QuillWizards</a>
                </div>`;
            adBox.id = "qw-ad-box";
            break;

        case 2:
            ad_html = `
                <span><strong>Struggling with assignments or academic papers?</strong> Let QuillWizards handle the stress.</span>
                <a href="https://quillwizards.com" target="_blank" class="qw-banner-btn">Learn More</a>
                <button id="qw-close-btn">&times;</button>
            `;
            adBox.id = "qw-slide-banner";
            break;
    
        case 3:
            ad_html = `
                <div class="qw-chat-header">
                    💬 Need Academic Help?
                    <button id="qw-close-btn">&times;</button>
                </div>
                <div class="qw-chat-body">
                    <p><strong>QuillWizards</strong> delivers top-grade essays, research, and more!</p>
                    <a href="https://quillwizards.com" target="_blank">Start Now</a>
                </div>
            `;
            adBox.id = "qw-chat-bubble";
            break;
        default:
            break;
    }
    
    adBox.innerHTML = ad_html;
    document.body.appendChild(adBox);
    document.getElementById("qw-close-btn").addEventListener("click", () => {
        adBox.remove();
    });

    // fade in animation
    setTimeout(() => {
        adBox.style.opacity = "1";
        adBox.style.transform = "translateY(0)";
        adBox.style.bottom = "20px";
    }, 200);
}
