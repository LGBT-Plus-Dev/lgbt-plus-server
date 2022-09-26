//NodeJS Modules
const http = require('http');
const app = require('express');

//Express Settings
app.use(cors());
app.use(express.json());
app.use(express.static('build'));
app.use(express.urlencoded({
  extended: true
}));
app.options('*', cors());

//Server Configs
const config = require('./config');

//Initialize API
const initApi = require('./routes/api');

//Create server
const server = http.createServer(function (req, res) {

});

//Listen to any request
server.listen(config.server.port, () => {
  console.log(`Server is running on port ${config.server.url}:${config.server.port}...`);
});

initApi(app);

