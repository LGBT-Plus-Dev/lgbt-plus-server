const Controller = require("./Controller");
const ServicePriceController = require("./service_price.controller");

module.exports = class ServiceController extends Controller {

  
  constructor() {

    var table = 'services';
    var hidden = [];

    super(table, hidden);

    this.priceController = new ServicePriceController();
  }

  /**
   * override
   */
  getList = async () => {
    let res = await this.qb.select().where().call();
    
    //Get Prices
    for(let item of res) {
      item.prices = await this.priceController.getPricesByServiceId(item.id);
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

      //Get Prices
      res.prices = await this.priceController.getPricesByServiceId(res.id);

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
      let prices = query.prices;
      delete query['prices'];
      let res = await this._add(query);

      if(prices) {
        let collection = await this.priceController.create(prices, res.id);
        res.prices = collection;
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
        let prices = query.prices;
        delete query['prices'];

        if(prices) {
          await this.priceController.deleteByServiceId(serviceId);
          await this.priceController.create(prices, serviceId);
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
      await this.priceController.deleteByServiceId(serviceId);
      return await this._deleteById(serviceId);
    }
  }
  
}