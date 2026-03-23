import os
import re

def process_file(filepath, is_index=False):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # 1. Standardize Footer
    footer_regex = re.compile(r'<footer class="footer-sito">.*?</footer>', re.DOTALL)
    
    # Path logic
    img_path = "../assets/images/logo1.png" if not is_index else "assets/images/logo1.png"
    html_prefix = "" if not is_index else "html/"
    termini_link = "termini.html" if not is_index else "html/termini.html"
    privacy_link = "privacy.html" if not is_index else "html/privacy.html"

    # Some pages in html folder might link to html/termini.html mistakenly, let's just make sure it's correct
    if not is_index:
        img_path = "../assets/images/logo1.png"
        termini_link = "termini.html"
        privacy_link = "privacy.html"
    else:
        # index.html case
        # wait, index.html had logo1.png as ../assets/images/logo1.png before, let's fix it properly.
        # But wait, style.css is fetched via href="assets/css/style.css" in index.html, so index is in the root!
        img_path = "assets/images/logo1.png"
        termini_link = "html/termini.html"
        privacy_link = "html/privacy.html"

    standard_footer = f"""<footer class="footer-sito">
        <hr aria-hidden="true">
        <div class="footer-contenuto">
            <div class="footer-logo">
                <img src="{img_path}" alt="Patavium Open" width="120">
            </div>
            <nav class="footer-nav" aria-label="Informazioni legali">
                <ul>
                    <li><a href="{termini_link}">Termini e condizioni</a></li>
                    <li><a href="{privacy_link}">Informativa sulla privacy</a></li>
                </ul>
            </nav>
        </div>
        <p class="footer-copyright">&copy; 2026 Patavium Open. Tutti i diritti riservati.</p>
    </footer>"""

    if "<footer" in content:
        content = footer_regex.sub(standard_footer, content)
    else:
        # Append footer if missing before </body>
        content = content.replace("</body>", f"{standard_footer}\n</body>")

    # 2. Fix Profilo / Carrello Alt texts
    # In index.html: <img src="assets/images/user.svg" alt="profilo" ...>
    # In others: <img src="../assets/images/user.svg" alt="profilo" ...>
    content = re.sub(r'alt="profilo"', 'alt=""', content)
    content = re.sub(r'alt="carrello"', 'alt=""', content)
    
    # Also catch other variations if any
    content = re.sub(r'alt="Logo ufficiale.*?"', 'alt="Patavium Open"', content)
    
    # 3. Header logo link paths
    if is_index:
        content = content.replace('src="../assets/images/logo1.png"', 'src="assets/images/logo1.png"')
    else:
        # Ensure it's correct in other pages
        content = content.replace('src="logo.png"', 'src="../assets/images/logo1.png"')
        
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

base_dir = r"c:\Users\marin\OneDrive\Desktop\tecweb_2026\progetto_tecweb_2026"

# Process root index.html
index_path = os.path.join(base_dir, "index.html")
if os.path.exists(index_path):
    process_file(index_path, is_index=True)

# Process all html in html folder
html_dir = os.path.join(base_dir, "html")
if os.path.exists(html_dir):
    for f in os.listdir(html_dir):
        if f.endswith(".html"):
            process_file(os.path.join(html_dir, f), is_index=False)

print("All files processed.")
