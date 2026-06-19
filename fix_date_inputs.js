import fs from 'fs';
import path from 'path';

function walkDir(dir, callback) {
    fs.readdirSync(dir).forEach(f => {
        let dirPath = path.join(dir, f);
        let isDirectory = fs.statSync(dirPath).isDirectory();
        if (isDirectory) {
            walkDir(dirPath, callback);
        } else {
            callback(path.join(dir, f));
        }
    });
}

function processFiles() {
    const viewsDir = 'c:\\Users\\PC\\Documents\\Dev\\e-daily\\e-daily-report\\resources\\views';
    let changedFiles = 0;

    walkDir(viewsDir, function(filePath) {
        if (!filePath.endsWith('.blade.php')) return;
        
        let content = fs.readFileSync(filePath, 'utf8');
        let originalContent = content;

        // Pattern to find <input ... type="date"|"time" ... >
        // We use a regex that matches the entire input tag
        const inputRegex = /<input([^>]*?)type=["'](date|time)["']([^>]*?)>/gi;

        content = content.replace(inputRegex, (match, beforeType, type, afterType) => {
            // If already wrapped (has data-enhanced="true" or something), skip
            if (match.includes('data-enhanced="true"')) {
                return match;
            }

            let fullAttributes = beforeType + ` type="${type}" ` + afterType;

            // Make sure we have pr-10 in class
            if (fullAttributes.includes('class="')) {
                fullAttributes = fullAttributes.replace(/class=["']([^"']*)["']/, (clsMatch, cls) => {
                    if (!cls.includes('pr-10')) {
                        return `class="${cls} pr-10 cursor-pointer"`;
                    }
                    return `class="${cls} cursor-pointer"`;
                });
            } else {
                fullAttributes += ' class="pr-10 cursor-pointer"';
            }

            // Add data-enhanced
            fullAttributes += ' data-enhanced="true"';

            let iconClass = type === 'time' ? 'fa-clock' : 'fa-calendar-alt';

            let newTag = `<div class="relative w-full">
    <input${fullAttributes}>
    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center text-slate-400 hover:text-[#1C7C54] hover:bg-emerald-50 rounded-full transition-colors z-10" onclick="if(this.previousElementSibling.showPicker) this.previousElementSibling.showPicker(); else this.previousElementSibling.focus();">
        <i class="fas ${iconClass}"></i>
    </button>
</div>`;
            return newTag;
        });

        if (content !== originalContent) {
            fs.writeFileSync(filePath, content, 'utf8');
            changedFiles++;
            console.log("Updated: " + filePath);
        }
    });

    console.log(`\nFinished! Modified ${changedFiles} files.`);
}

processFiles();
