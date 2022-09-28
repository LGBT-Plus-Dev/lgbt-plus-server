const Controller = require("./Controller");

module.exports = class ClientController extends Controller {

  
  constructor() {

    var table = 'clients';
    var hidden = ['password'];

    super(table, hidden);
  }

  /**
   * Login
   */
  authenticate = async (req) => {
    let query = req.query;
    let res = await this.qb.select().where({
      email: query.email,
      password: query.password
    }).first();
    res = this._hideColumns(res);
    return res;
  }

  /**
   * Get Active Collection
   */
  getActive = async () => {
    let res = await this.qb.select().where({
      status: 1
    }).call();
    res = this._hideColumns(res);
    return res;
  }

  getById = async(req) => {
    let clientId = req.params.id;
    let res = await this._getById(clientId);
    return res;
  }

  /**
   * Create or Update
   */
  save = async (req) => {

    let clientId = req.params.id;
    if(!req.body.client) {
      return {
        error: "Missing parameter `client`"
      }
    }

    //Create
    if(clientId === undefined) {
      return await this._add(req.body.client);
    }
    
    //Update
    else {

      if(clientId === ":id") {
        return {
          error: "Missing route parameter `id`"
        }
      }
      else {

        return await this._updateById(clientId, req.body.client);
      }
    }
  }

  /**
   * Delete By Id
   */
  deleteById = async (req) => {
    let clientId = req.params.id;
    if(clientId === ":id") {
      return {
        error: "Missing route parameter `id`"
      }
    }
    else {
      return await this._deleteById(clientId);
    }
  }
  
}