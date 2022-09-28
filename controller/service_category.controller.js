const Controller = require("./Controller");

module.exports = class ServiceCategoryController extends Controller {

  
  constructor() {

    var table = 'service_categories';
    var hidden = [];

    super(table, hidden);
  }

  getCategoriesByServiceId = async(serviceId) => {
    let res = await this.qb.select().where({
      service: serviceId
    }).order({
      price: 1
    }).call();
    return res;
  }

  /**
   * Create
   */
  create = async (categories, serviceId) => {
    let collections = [];

    for(let category of categories) {
      category.service = serviceId;
      let res = await this._add(category);
      collections = [...collections, res];
    }

    return collections;
  }

  /**
   * Delete By Service Id
   */
  deleteByServiceId = async (serviceId) => {
    return await this.qb.delete().where({
      service: serviceId
    }).call();
  }
  
}