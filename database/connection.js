const mysql = require('mysql2');
const util = require('util');

/**
 * Databse config
 * 
 * db: {
 *  host: 'localhost',
 *  port: 3306,
 *  database: 'sample',
 *  username: 'root',
 *  password: 'root'
 * }
 */
const config = require('../config');

/**
 * Create Connection
 */
const conn = mysql.createConnection(config.db);

conn.connect(err => {
    if(err) console.warn("ERROR",err);
    else console.log("Database Connected");
})

const db = util.promisify(conn.query).bind(conn);

module.exports = db;