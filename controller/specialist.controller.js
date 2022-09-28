const Controller = require("./Controller");
const SpecialtyController = require("./specialist_service.controller");

module.exports = class SpecialistController extends Controller {

  
  constructor() {

    var table = 'specialists';
    var hidden = ['password'];

    super(table, hidden);

    this.specialtyController = new SpecialtyController();
  }

  /**
   * override
  */
  getList = async () => {
    let res = await this.qb.select().where().call();
    
    //Get Services
    for(let item of res) {
      item.services = await this.specialtyController.getSpecialistServices(item.id);
    }

    return res;
  }

  /**
   * override
   */
  _getById = async (id) => {
    
    let res = await this.qb.select().where({
      id: id
    }).first();

    if(res) {
      res = this._hideColumns(res);

      //Get Services
      res.services = await this.specialtyController.getSpecialistServices(res.id);

      return res;
    }
    else{
      return null;
    }
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
      let query = req.body.specialist;
      let services = query.services;
      delete query['services'];
      let res = await this._add(query);

      if(services) {
        let collection = await this.specialtyController.create(services, res.id);
        res.services = collection;
      }

      return res;
    }
    
    //Update
    else {

      if(specialistId === ":id") {
        return {
          error: "Missing route parameter `id`"
        }
      }
      else {
        let query = req.body.specialist;
        let services = query.services;
        delete query['services'];

        if(services) {
          await this.specialtyController.deleteBySpecialistId(specialistId);
          await this.specialtyController.create(services, specialistId);
        }
        return await this._updateById(specialistId, query);
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