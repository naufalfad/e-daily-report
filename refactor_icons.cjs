const fs = require('fs');
const path = require('path');

function walkDir(dir, callback) {
    if (!fs.existsSync(dir)) return;
    fs.readdirSync(dir).forEach(f => {
        let dirPath = path.join(dir, f);
        if (fs.statSync(dirPath).isDirectory()) {
            walkDir(dirPath, callback);
        } else {
            callback(dirPath);
        }
    });
}

const targetDirs = [
    'resources/views',
    'resources/js'
];

let changedFilesCount = 0;

targetDirs.forEach(dir => {
    walkDir(dir, file => {
        if (!file.endsWith('.blade.php') && !file.endsWith('.js')) return;
        
        let originalContent = fs.readFileSync(file, 'utf8');
        let content = originalContent;

        // 1. Compact paddings in buttons/links (global regex for common tailwind padding combos)
        // Only target when they are combined as padding classes.
        content = content.replace(/\bpx-8\s+py-3\b/g, 'px-4 py-2');
        content = content.replace(/\bpx-6\s+py-3\b/g, 'px-4 py-2');
        content = content.replace(/\bpx-5\s+py-2\.5\b/g, 'px-4 py-2');

        // 2. Reduce gap
        content = content.replace(/\bgap-3\b/g, 'gap-1.5');
        content = content.replace(/\bgap-4\b/g, 'gap-2'); // gap-4 is quite large for buttons/icon groups

        // 3. Compact margin specifically inside <i> tags for icons
        // We look for <i class="... mr-2 ...">
        content = content.replace(/<i\s+class="([^"]*)\bmr-2\b([^"]*)"/g, '<i class="$1mr-1.5$2"');
        content = content.replace(/<i\s+class="([^"]*)\bml-2\b([^"]*)"/g, '<i class="$1ml-1.5$2"');
        
        // Also fix single quote cases in JS
        content = content.replace(/<i\s+class='([^']*)\bmr-2\b([^']*)'/g, "<i class='$1mr-1.5$2'");
        content = content.replace(/<i\s+class='([^']*)\bml-2\b([^']*)'/g, "<i class='$1ml-1.5$2'");

        // 4. Remove text-lg, text-xl, text-2xl from icons to normalize size
        content = content.replace(/<i\s+class="([^"]*)\btext-(lg|xl|2xl|3xl|4xl)\b([^"]*)"/g, '<i class="$1$3"');
        content = content.replace(/<i\s+class='([^']*)\btext-(lg|xl|2xl|3xl|4xl)\b([^']*)'/g, "<i class='$1$3'");

        // 5. Clean up duplicate spaces inside class strings that might have been left behind
        content = content.replace(/class="([^"]*)"/g, (match, p1) => {
            return `class="${p1.replace(/\s+/g, ' ').trim()}"`;
        });
        content = content.replace(/class='([^']*)'/g, (match, p1) => {
            return `class='${p1.replace(/\s+/g, ' ').trim()}'`;
        });

        // 6. Duplicate Icon Removal in Buttons
        // Regex to find buttons with two identical or similar icons
        // For safety, we only remove if a button literally has two <i> tags. 
        // We will match <button> ... </button>
        content = content.replace(/<button[^>]*>[\s\S]*?<\/button>/g, (btnHtml) => {
            let iTags = btnHtml.match(/<i\s+class=["'][^"']*fa-[^"']*["'][^>]*>[\s\S]*?<\/i>/g);
            if (iTags && iTags.length > 1) {
                // Remove all but the first icon if it's considered a "duplication error"
                // Let's only remove the trailing one if they are separated by text.
                // We'll just replace the last matched <i> with ''
                let lastIcon = iTags[iTags.length - 1];
                let firstIcon = iTags[0];
                
                // If it's a "dropdown" or "arrow" we might want to keep it.
                // Let's check if the last icon is a chevron/arrow
                if (!lastIcon.includes('chevron') && !lastIcon.includes('arrow') && !lastIcon.includes('caret')) {
                    // Remove the last icon to prevent duplicate icons like save + check
                    let lastIndex = btnHtml.lastIndexOf(lastIcon);
                    btnHtml = btnHtml.substring(0, lastIndex) + btnHtml.substring(lastIndex + lastIcon.length);
                }
            }
            return btnHtml;
        });

        if (content !== originalContent) {
            fs.writeFileSync(file, content, 'utf8');
            changedFilesCount++;
            console.log("Compacted: " + file);
        }
    });
});

console.log(`\nRefactoring Complete. Modified ${changedFilesCount} files.`);
