var wpTribuCollectionsPosts = ( function() {
	return {
		actions: {
			initCategorySticky: {
				handle: 'initCategorySticky',
				on: 'initialize',
				doThis: function( args ) {
					var collection = args.collection;
					collection.comparison = 'categorySticky';
				},
				priority: 20
			}
		},

		comparators: {
			categorySticky: function( post1, post2 ) {
				var post1isCategorySticky = -1 !== post1.get( 'cssClasses').split( ' ' ).indexOf( 'category-sticky' ),
					post2isCategorySticky = -1 !== post2.get( 'cssClasses').split( ' ' ).indexOf( 'category-sticky' );

				if ( ( post1.isSticky() === post2.isSticky() ) && ( post1isCategorySticky === post2isCategorySticky )  ) {
					var orderSign = ( 'DESC' === o2.options.order ) ? -1 : 1;
					return orderSign * o2.Utilities.compareTimes( post1.get( 'unixtime' ), post2.get( 'unixtime' ) );
				} else if ( post1.isSticky() ) {
					return -1;
				} else if ( post1isCategorySticky ) {
					return -1;
				}

				return 1;
			}
		}
	};
} )();

Cocktail.mixin( o2.Collections.Posts, wpTribuCollectionsPosts );
