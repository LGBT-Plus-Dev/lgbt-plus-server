/**
 * @author: Emerson Dalwampo <dalwampoemersons@gmail.com>
 * Date: 09.25.22
 *
 */

import db from "../connection";

class QueryBuilder {
  constructor(methodName) {

    this._raw = '';
    this._queryType = 'select';
    this._fields = '*';
    this._table = null;
    this._where = {};
    this._order = {};
    this._group = [];
    this._limit = 50;
    this._offset = 0;
    this._handler = null;
    this._set = {};
    this._join = [];
    this._rightJoin = [];
    this._leftJoin = [];

    this.processor = null;

    this._loadMethod(methodName);
  }
  async raw(query) {
    var result = await db(query);
    return result;
  }
  select(clause) {

    if (undefined !== clause) {
      this.where(clause);
    }

    this._queryType = 'select';

    return this;
  }
  count(clause) {

    if (undefined !== clause) {
      this.where(clause);
    }

    this._queryType = 'count';

    return this;
  }
  update(clause) {

    if (undefined !== clause) {
      this.set(clause);
    }

    this._queryType = 'update';

    return this;
  }
  delete(clause) {

    if (undefined !== clause) {
      this.where(clause);
    }

    this._queryType = 'delete';

    return this;
  }
  insert(clause) {

    if (undefined !== clause) {
      this.set(clause);
    }

    this._queryType = 'insert';

    return this;
  }
  async call() {

    var query = this.processor.query(this);
    var result = await db(query);
    return result;
  }
  async first() {

    var query = this.processor.query(this);
    var result = await db(query);

    if (result && result.length > 0) {
      return result[0];
    }
    else {
      return null;
    }
  }
  set(object) {

    this._set = object || {};

    return this;
  }
  fields(columns) {

    this._fields = columns;

    return this;
  }
  table(table) {

    this._table = table;

    return this;
  }
  where(clause) {

    this._where = clause;

    return this;
  }
  or(clause) {

    return this;
  }
  order(order) {

    this._order = order;

    return this;
  }
  join(join) {

    this._join = join;

    return this;
  }
  rightJoin(rightJoin) {

    this._rightJoin = rightJoin;

    return this;
  }
  leftJoin(leftJoin) {

    this._leftJoin = leftJoin;

    return this;
  }
  group(group) {

    this._group = group;

    return this;
  }
  limit(limit) {

    this._limit = limit;

    return this;
  }
  offset(offset) {

    this._offset = offset;

    return this;
  }
  handler(fnc) {

    this._handler = fnc;

    return this;
  }
  _loadMethod(methodName) {
    try {
      this.processor = require('./mysql');
    } catch (e) {
      throw "Method " + methodName + " corrupt: " + e;
    }
  }
}























QueryBuilder.prototype.and = QueryBuilder.prototype.where;



export default QueryBuilder;