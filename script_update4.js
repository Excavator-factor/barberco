const fs = require('fs');
let content = fs.readFileSync('c:/laragon/www/barbershop/barber/dashboard.php', 'utf8');

// The main forms for start/finish queue
content = content.replace(/<form method="POST" action="">/g, '<form method="POST" action="../functions/crud_antrean.php">');

// The form for toggle_status
// Let's just find <input type="hidden" name="action" value="toggle_status"> and replace its parent form's action.
// Using regex to add action to any form that just says method="POST" (with varying classes) if needed.
content = content.replace(/<form method="POST" class="([^"]*)">/g, (match, p1) => {
    // If it has toggle_status inside? Actually we'll just check if it's the toggle status form
    if (p1.includes('inline-block') || p1.includes('relative')) {
        return `<form method="POST" action="../functions/crud_barber.php" class="${p1}">`;
    }
    return match;
});

// For forms that have no action attribute but have class or other things
content = content.replace(/<form method="POST"(?! action)/g, '<form method="POST" action="../functions/crud_antrean.php"');

fs.writeFileSync('c:/laragon/www/barbershop/barber/dashboard.php', content);
console.log('Fixed dashboard forms');
