const fs = require('fs');
let content = fs.readFileSync('c:/laragon/www/barbershop/barber/_bootstrap.php', 'utf8');

const sToken = "// Handle form POST actions globally or in page controller";
const eToken = "function barber_flash(";

// Oh wait, `barber_flash` is actually BEFORE the `Handle form POST actions`.
const targetStart = content.indexOf(sToken);
const endSnippet = "if ($barberId > 0) {";
const targetEnd = content.indexOf(endSnippet);

if (targetStart !== -1 && targetEnd !== -1) {
    const before = content.substring(0, targetStart);
    const after = content.substring(targetEnd);

    // We want to just keep $today, $activeQueue etc initialization which is after the POST block.
    // The POST block ends where `$today = date('Y-m-d');` begins. Let's find $today = date...
    const pureEnd = content.indexOf("$today = date('Y-m-d');", targetStart);

    if (pureEnd !== -1) {
        fs.writeFileSync('c:/laragon/www/barbershop/barber/_bootstrap.php', before + '\n' + content.substring(pureEnd));
        console.log('Removed inline POST from barber bootstrap');
    }
}
