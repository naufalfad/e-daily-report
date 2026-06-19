const fs = require('fs');
const path = require('path');
function walkDir(dir, callback) {
    fs.readdirSync(dir).forEach(f => {
        let dirPath = path.join(dir, f);
        if (fs.statSync(dirPath).isDirectory()) {
            walkDir(dirPath, callback);
        } else {
            callback(dirPath);
        }
    });
}
walkDir('resources/views', file => {
    if (!file.endsWith('.blade.php')) return;
    let content = fs.readFileSync(file, 'utf8');
    if (content.includes('/ data-enhanced="true"')) {
        let newContent = content.replace(/\/ data-enhanced="true"/g, ' data-enhanced="true"');
        fs.writeFileSync(file, newContent, 'utf8');
        console.log("Fixed " + file);
    }
});
