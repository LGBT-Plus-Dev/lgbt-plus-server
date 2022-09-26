const Controller = require("./Controller");

module.exports = class AdminController extends Controller {

  constructor() {
    super('users');
  }

  getAllUsers = async () => {
    let res = await this.qb.select().call();
    for(let item of res) {
      delete item['password'];
    }
    return res;
  }

  authenticate = async (req) => {
    let res = await this.qb.select().where({
      username: req.username,
      password: req.password
    }).first();

    if(res)
      delete res['password'];

    return res;
  }



  
}