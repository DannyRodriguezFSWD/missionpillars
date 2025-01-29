export default {
    /**
     * Thanks https://www.jstips.co/en/javascript/picking-and-rejecting-object-properties/ 
     * @param  {[object]} obj  
     * @param  {[array]} keys Array of strings represent keys to be kept
     * @return {[object]}      A new object containing only keys from the object that are picked
     */
    object_pick(obj, keys) {
    return keys.map(k => k in obj ? {[k]: obj[k]} : {})
               .reduce((res, o) => Object.assign(res, o), {});
    },
    
    /**
     * Thanks https://www.jstips.co/en/javascript/picking-and-rejecting-object-properties/ 
     * @param  {[object]} obj  
     * @param  {[array]} keys Array of strings represent keys to be excluded
     * @return {[object]}      A new object with keys from the object rejected excluded
     */
     object_reject(obj, keys) {
         const vkeys = Object.keys(obj)
             .filter(k => !keys.includes(k));
         return pick(obj, vkeys);
     },

     /**
      * Returns an object containing indexes with the values of a specified key
      * @param  {[object]} collection An object that is a collection of similar objects (properties are similar to array indexes)
      * @param  {[string]} key        The key of the individual objects to be returned
      * @return {[string]}            A new object containing values
      */
     object_pluck(collection, key) {
         return collection.map(object => {
             return object[key]
         })
     }
}
