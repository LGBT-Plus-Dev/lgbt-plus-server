const Controller = require("./Controller");

module.exports = class SpecialistController extends Controller {

  
  constructor() {

    var table = 'specialists';
    var hidden = ['password'];

    super(table, hidden);
  }

  /**
   * Login
   */
  authenticate = async (req) => {
    let query = req.query;
    let res = await this.qb.select().where({
      username: query.username,
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
    let specialistId = req.params.id;
    let res = await this._getById(specialistId);
    return res;
  }

  /**
   * Create or Update
   */
  save = async (req) => {

    let specialistId = req.params.id;
    if(!req.body.specialist) {
      return {
        error: "Missing parameter `specialist`"
      }
    }

    //Create
    if(specialistId === undefined) {
      return await this._add(req.body.specialist);
    }
    
    //Update
    else {

      if(specialistId === ":id") {
        return {
          error: "Missing route parameter `id`"
        }
      }
      else {

        return await this._updateById(specialistId, req.body.specialist);
      }
    }
  }

  /**
   * Delete By Id
   */
  deleteById = async (req) => {
    let specialistId = req.params.id;
    if(specialistId === ":id") {
      return {
        error: "Missing route parameter `id`"
      }
    }
    else {
      return await this._deleteById(specialistId);
    }
  }
  
}