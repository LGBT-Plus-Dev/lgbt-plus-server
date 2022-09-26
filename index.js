/**
 * NodeJS Modules
 */
const express = require('express');
const cors = require('cors');

/**
 * Express Settigns
 */
const app = express();
app.use(cors());
app.use(express.json());
app.use(express.static('build'));
app.use(express.urlencoded({
  extended: true
}));
app.options('*', cors());

/**
 * Server Configs
 */
const config = require('./config');

/**
 * Initialize API
 */
const initApi = require('./routes/api');

/**
 * Listen to Any Request
 */
app.listen(config.server.port, () => {
  console.log(`Server is running on port ${config.server.url}:${config.server.port}...`);
});

app.get("/", (req, res) => {
  res.send("Hello World");
})

initApi(app);

