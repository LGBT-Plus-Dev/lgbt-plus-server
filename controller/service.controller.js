const Controller = require("./Controller");
const ServiceCategoryController = require("./service_category.controller");
const SpecialtyController = require("./specialist_service.controller");
module.exports = class ServiceController extends Controller {

  
  constructor() {

    var table = 'services';
    var hidden = [];

    super(table, hidden);

    this.categoryController = new ServiceCategoryController();
    this.specialtyController = new SpecialtyController();
  }

  /**
   * override
   */
  getList = async () => {
    let res = await this.qb.select().where().call();
    
    //Get Categories
    for(let item of res) {
      item.categories = await this.categoryController.getCategoriesByServiceId(item.id);
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

      //Get Categories
      res.categories = await this.categoryController.getCategoriesByServiceId(res.id);

      return res;
    }
    else{
      return null;
    }
  }

  getById = async(req) => {
    let serviceId = req.params.id;
    let res = await this._getById(serviceId);
    return res;
  }

  /**
   * Create or Update
   */
  save = async (req) => {

    let serviceId = req.params.id;
    if(!req.body.service) {
      return {
        error: "Missing parameter `service`"
      }
    }

    //Create
    if(serviceId === undefined) {
      
      let query = req.body.service;
      let categories = query.categories;
      delete query['categories'];
      let res = await this._add(query);

      if(categories) {
        let collection = await this.categoryController.create(categories, res.id);
        res.categories = collection;
      }
      
      return res;
    }
    
    //Update
    else {

      if(serviceId === ":id") {
        return {
          error: "Missing route parameter `id`"
        }
      }
      else {
        let query = req.body.service;
        let categories = query.categories;
        delete query['categories'];

        if(categories) {
          await this.categoryController.deleteByServiceId(serviceId);
          await this.categoryController.create(categories, serviceId);
        }
        
        return await this._updateById(serviceId, query);
      }
    }
  }

  /**
   * Delete By Id
   */
  deleteById = async (req) => {
    let serviceId = req.params.id;
    if(serviceId === ":id") {
      return {
        error: "Missing route parameter `id`"
      }
    }
    else {
      await this.categoryController.deleteByServiceId(serviceId);
      await this.specialtyController.deleteByServiceId(serviceId);
      return await this._deleteById(serviceId);
    }
  }
  
}