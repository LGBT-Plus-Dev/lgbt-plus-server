const Controller = require("./Controller");

module.exports = class UserController extends Controller {

  constructor() {
    super('users');
  }

  getAllUsers = async (req) => {
    let res = await this.qb.select().where({uuid: req.uuid})
  }

  
}