const Controller = require("./Controller");

module.exports = class AdminController extends Controller {

  constructor() {

    var table = 'users';
    var hidden = ['password'];

    super(table, hidden);
  }

  authenticate = async (req) => {
    let res = await this.qb.select().where({
      username: req.query.username,
      password: req.query.password
    }).first();

    res = this._hideColumns(res);
    return res;
  }



  
}