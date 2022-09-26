const Controller = require("./Controller");

module.exports = class AdminController extends Controller {

  constructor() {

    var table = 'users';
    var hidden = ['passwords'];

    super(table, hidden);
  }

  authenticate = async (req) => {
    let res = await this.qb.select().where({
      username: req.username,
      password: req.password
    }).first();
    res = this._hideColumns(res);
    return res;
  }



  
}