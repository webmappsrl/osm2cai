-- This file create two layers with info about hiking routes, specially
-- for CAI

-- Copyright Luca Delucchi 2020
-- License Creative Commons BY 4.0

local tables = {}

tables.hiking_routes = osm2pgsql.define_relation_table('hiking_routes_osm', {
    { column = 'name', type = 'text' },
    { column = 'cai_scale', type = 'text' },
    { column = 'osmc_symbol', type = 'text' },
    { column = 'network', type= 'text' },
    { column = 'survey_date', type= 'text' },
    { column = 'roundtrip', type= 'text' },
    { column = 'symbol', type= 'text' },
    { column = 'symbol_it', type= 'text' },
    { column = 'ascent', type= 'text' },
    { column = 'descent', type= 'text' },
    { column = 'distance', type= 'text' },
    { column = 'duration_forward', type= 'text' },
    { column = 'duration_backward', type= 'text' },
    { column = 'from', type= 'text' },
    { column = 'to', type= 'text' },
    { column = 'rwn_name', type= 'text' },
    { column = 'ref_REI', type= 'text' },
    { column = 'maintenance', type= 'text' },
    { column = 'maintenance_it', type= 'text' },
    { column = 'operator', type= 'text' },
    { column = 'state', type= 'text' },
    { column = 'ref', type= 'text' },
    { column = 'source', type= 'text' },
    { column = 'source_ref', type= 'text' },
    { column = 'note', type= 'text' },
    { column = 'note_it', type= 'text' },
    { column = 'old_ref', type= 'text' },
    { column = 'note_project_page', type= 'text' },
    { column = 'website', type= 'text' },
    { column = 'wikimedia_commons', type= 'text' },
    { column = 'description', type= 'text' },
    { column = 'description_it', type= 'text' },
    { column = 'tags', type = 'hstore' },
    { column = 'geom', type = 'multilinestring' },
})

tables.hiking_ways = osm2pgsql.define_way_table('hiking_ways_osm', {
    { column = 'trail_visibility', type='text'},
    { column = 'sac_scale', type='text'},
    { column = 'tracktype', type='text'},
    { column = 'highway', type='text'},
    { column = 'name', type='text'},
    { column = 'ref', type='text'},
    { column = 'access', type='text'},
    { column = 'incline', type='text'},
    { column = 'surface', type='text'},
    { column = 'ford', type='bool'},
    { column = 'tags', type = 'hstore' },
    { column = 'geom', type = 'linestring' },
    { column = 'rel_refs', type = 'text' }, -- for the refs from the relations
    { column = 'rel_ids',  type = 'int8' }, -- array with integers (for relation IDs)
})

local w2r = {}

function osm2pgsql.process_way(object)
    if not object.tags.highway then
        return
    end
    local row = {
	trail_visibility = object.tags.trail_visibility,
	sac_scale = object.tags.sac_scale,
	tracktype = object.tags.tracktype,
	highway = object.tags.highway,
	name = object.tags.name,
	ref = object.tags.ref,
	access = object.tags.access,
	incline = object.tags.incline,
	surface = object.tags.surface,
	ford = object.tags.ford,
	tags = object.tags.tags
    }
    
    local d = w2r[object.id]
    if d then
        local refs = {}
        local ids = {}
        for rel_id, rel_ref in pairs(d) do
            refs[#refs + 1] = rel_ref
            ids[#ids + 1] = rel_id
        end
        table.sort(refs)
        table.sort(ids)
        row.rel_refs = table.concat(refs, ',')
        row.rel_ids = '{' .. table.concat(ids, ',') .. '}'
    end
    tables.hiking_ways:add_row(row)
end

function osm2pgsql.process_relation(object)
    if object.tags.type == 'route' and object.tags.route == 'hiking' then
        tables.hiking_routes:add_row({
            tags = object.tags,
	    name = object.tags.name,
	    cai_scale = object.tags.cai_scale,
	    osmc_symbol = object.tags['osmc:symbol'],
	    network = object.tags.network,
	    survey_date = object.tags['survey:date'],
	    roundtrip = object.tags.roundtrip,
	    symbol = object.tags.symbol,
	    symbol_it = object.tags['symbol:it'],
	    ascent = object.tags.ascent,
	    descent = object.tags.descent,
	    distance = object.tags.distance,
	    duration_forward = object.tags['duration:forward'],
	    duration_backward = object.tags['duration:backward'],
	    from = object.tags.from,
	    to = object.tags.to,
	    rwn_name = object.tags['rwn:name'],
	    ref_REI = object.tags['ref:REI'],
	    maintenance = object.tags.maintenance,
	    maintenance_it = object.tags['maintenance:it'],
	    operator = object.tags.operator,
	    state = object.tags.state,
	    ref = object.tags.ref,
	    source = object.tags.source,
	    source_ref = object.tags['source:ref'] or object.tags.source_ref,
	    note = object.tags.note,
	    note_it = object.tags['note:it'],
	    old_ref = object.tags.old_ref,
	    note_project_page = object.tags['note:project_page'],
	    website = object.tags.website,
	    wikimedia_commons = object.tags.wikimedia_commons,
	    description = object.tags.description,
	    description_it = object.tags['description:it'],
	    geom = { create = 'line' },
        })
	for _, member in ipairs(object.members) do
            if member.type == 'w' then
                if not w2r[member.ref] then
                    w2r[member.ref] = {}
                end
                w2r[member.ref][object.id] = object.tags.ref
            end
        end
    end
end
