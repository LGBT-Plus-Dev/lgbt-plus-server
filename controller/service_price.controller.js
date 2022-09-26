const Controller = require("./Controller");

module.exports = class ServicePriceController extends Controller {

  
  constructor() {

    var table = 'service_prices';
    var hidden = [];

    super(table, hidden);
  }

  getPricesByServiceId = async(serviceId) => {
    let res = await this.qb.select().where({
      service: serviceId
    }).call();
    return res;
  }

  getById = async(priceId) => {
    let res = await this._getById(priceId);
    return res;
  }

  /**
   * Create
   */
  create = async (prices, serviceId) => {
    let collections = [];

    for(let price of prices) {
      price.service = serviceId;
      let res = await this._add(price);
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