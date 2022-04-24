package pt.ipleiria.estg.dei.ei.pi.AALBackend.ws;


import pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos.AdministratorDTO;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs.AdministratorBean;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Administrator;

import javax.annotation.security.RolesAllowed;
import javax.ejb.EJB;
import javax.ws.rs.*;
import javax.ws.rs.core.MediaType;
import javax.ws.rs.core.Response;
import java.util.List;
import java.util.stream.Collectors;

@Path("administrators") // relative url web path for this service
@Produces({MediaType.APPLICATION_JSON}) // injects header “Content-Type: application/json”
@Consumes({MediaType.APPLICATION_JSON}) // injects header “Accept: application/json”
public class AdministratorService {

    @EJB
    private AdministratorBean administratorBean;

    @GET
    @Path("/")
    @RolesAllowed({"Administrator"})
    public Response getAllAdministratorsWS() {
        return Response.status(Response.Status.OK)
                .entity(toDTOs(administratorBean.getAllAdministrators()))
                .build();
    }

    @GET
    @Path("{id}")
    @RolesAllowed({"Administrator"})
    public Response getAdministratorWS(@PathParam("id") long id) throws Exception {
        Administrator administrator = administratorBean.findAdministrator(id);

        return Response.status(Response.Status.OK)
                .entity(toDTO(administrator))
                .build();
    }

    @POST
    @Path("/")
    @RolesAllowed({"Administrator"})
    public Response createAdministratorWS(AdministratorDTO administratorDTO) throws Exception {
        long id = administratorBean.create(
                administratorDTO.getEmail(),
                administratorDTO.getPassword(),
                administratorDTO.getName());

        Administrator administrator = administratorBean.findAdministrator(id);

        return Response.status(Response.Status.CREATED)
                .entity(toDTO(administrator))
                .build();
    }

    @PUT
    @Path("{id}")
    @RolesAllowed({"Administrator"})
    public Response updateAdministratorWS(@PathParam("id") long id,AdministratorDTO administratorDTO) throws Exception {
        administratorBean.update(
                id,
                administratorDTO.getEmail(),
                administratorDTO.getName());

        Administrator administrator = administratorBean.findAdministrator(id);

        return Response.status(Response.Status.OK)
                .entity(toDTO(administrator))
                .build();
    }

    @DELETE
    @Path("{id}")
    @RolesAllowed({"Administrator"})
    public Response deleteAdministratorWS(@PathParam("id") long id) throws Exception {
        if (administratorBean.delete(id))
            return Response.status(Response.Status.OK)
                    .entity("Administrator "+id+" deleted!")
                    .build();

        return Response.status(Response.Status.INTERNAL_SERVER_ERROR)
                .build();
    }


    private List<AdministratorDTO> toDTOs(List<Administrator> administrators) {
        return administrators.stream().map(this::toDTO).collect(Collectors.toList());
    }

    private AdministratorDTO toDTO(Administrator administrator) {
        return new AdministratorDTO(
                administrator.getId(),
                administrator.getEmail(),
                administrator.getName());
    }
}
