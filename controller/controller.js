const Querybuilder = require('../querybuilder/qb');

class Controller {

  constructor (table) {
    this.table = table;
    this.qb = new Querybuilder('mysql').table(this.tableName);
  }

}

module.exports = Controller;