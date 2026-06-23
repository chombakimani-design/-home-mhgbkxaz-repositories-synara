const express = require('express');
const app = express();
const PORT = 3000;

app.get('/test', (req, res) => {
    console.log('Test endpoint hit!');
    res.json({ message: 'Working!' });
});

app.listen(PORT, () => {
    console.log('Test server running on port', PORT);
});
