const Querybuilder = require('../database/querybuilder/qb');

class Controller {

  constructor (table, hidden) {
    this.table = table;
    this.hidden = hidden;
    this.qb = new Querybuilder('mysql').table(this.table);
  }

  getList = async () => {
    let res = await this.qb.select().where().call();
    res = this._hideColumns(res);
    return res;
  }

  /**
   * 
   * Protected Methods
   * 
   */
  
  _hideColumns = (collections) => {

    if(!collections)
      return null;

    if(Array.isArray(collections)) {
      for(let item of collections) {
        for(let hide of this.hidden) {
          delete item[hide];
        }
      }
    }
    else {
      for(let hide of this.hidden) {
        delete collections[hide];
      }
    }

    return collections;
  }

  _getById = async (id) => {
    let res = await this.qb.select().where({
      id: id
    }).first();
    if(res) {
      res = this._hideColumns(res);
      return res;
    }
    else{
      return null;
    }
  }

  _add = async (item) => {
    let res = await this.qb.insert().set(item).call();
      
    if(res) 
      return await this._getById(res.insertId);
    else 
      return false;
  }

  _updateById = async (id, item) => {
    await this.qb.update().set(item).where({
      id: id
    }).call();

    return await this._getById(id);
  }

  _deleteById = async (id) => {
    let res = await this.qb.delete().where({
      id: id
    }).call();

    if(res && res.affectedRows > 0) 
      return true;
    else 
      return false;
  }
}

module.exports = Controller;