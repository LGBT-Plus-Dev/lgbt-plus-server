const Controller = require("./Controller");

module.exports = class ServiceCategoryController extends Controller {

  
  constructor() {

    var table = 'specialist_services';
    var hidden = [];

    super(table, hidden);
  }

  getSpecialistServices = async(specialistId) => {

    let res = await this.qb.raw(
      `SELECT specialist_services.*, services.name 
      FROM specialist_services
      LEFT JOIN services ON specialist_services.service = services.id
      WHERE specialist_services.specialist = ${specialistId}
      `
    );
    return res;
  }

  /**
   * Create
   */
  create = async (services, specialistId) => {
    let collections = [];

    for(let service of services) {

      let item = {
        service: service.service,
        specialist: specialistId
      }
      let res = await this._add(item);
      collections = [...collections, res];
    }

    return collections;
  }

  /**
   * Delete By Specialist Id
   */
  deleteBySpecialistId = async (specialistId) => {
    return await this.qb.delete().where({
      specialist: specialistId
    }).call();
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