const Querybuilder = require('../database/querybuilder/qb');

class Controller {

  constructor (table) {
    this.table = table;
    this.qb = new Querybuilder('mysql').table(this.table);
  }

}

module.exports = Controller;