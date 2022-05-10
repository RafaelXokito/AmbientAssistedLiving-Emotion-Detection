package pt.ipleiria.estg.dei.ei.pi.AALBackend.ws;

import pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos.AdministratorDTO;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos.ClientDTO;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs.ClientBean;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs.PersonBean;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Administrator;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Client;

import javax.annotation.security.RolesAllowed;
import javax.ejb.EJB;
import javax.ws.rs.*;
import javax.ws.rs.core.MediaType;
import javax.ws.rs.core.Response;
import java.util.List;
import java.util.stream.Collectors;

@Path("clients")
@Produces({MediaType.APPLICATION_JSON})
@Consumes({MediaType.APPLICATION_JSON})
@RolesAllowed({"Administrator"})
public class ClientService {

    @EJB
    private ClientBean clientBean;
    @EJB
    private PersonBean personBean;

    @GET
    @Path("/")
    public Response getAllClientsWS() {
        return Response.status(Response.Status.OK)
                .entity(toDTOs(clientBean.getAllCLients()))
                .build();
    }

    @GET
    @Path("{id}")
    public Response getClientWS(@PathParam("id") Long id) throws Exception {
        Client cLient = clientBean.findClient(id);

        return Response.status(Response.Status.OK)
                .entity(toDTO(cLient))
                .build();
    }

    @POST
    @Path("/")
    public Response createClientWS(ClientDTO clientDTO, @HeaderParam("Authorization") String auth) throws Exception {
        Long id = clientBean.create(clientDTO.getEmail(), clientDTO.getPassword(), clientDTO.getName(), clientDTO.getBirthDate(), clientDTO.getContact(),personBean.getPersonByAuthToken(auth).getId());

        Client client = clientBean.findClient(id);
        return Response.status(Response.Status.CREATED)
                .entity(toDTO(client))
                .build();
    }

    @PUT
    @Path("{id}")
    public Response updateClientWS(@PathParam("id") Long id,ClientDTO clientDTO) throws Exception {
        clientBean.update(id, clientDTO.getName(), clientDTO.getBirthDate(), clientDTO.getContact());

        Client client = clientBean.findClient(id);

        return Response.status(Response.Status.OK)
                .entity(toDTO(client))
                .build();
    }

    @DELETE
    @Path("{id}")
    public Response deleteClientWS(@PathParam("id") Long id) throws Exception {
        if (clientBean.delete(id))
            return Response.status(Response.Status.OK)
                    .entity("[Success] Client with id \'"+id+"\' was deleted")
                    .build();

        return Response.status(Response.Status.INTERNAL_SERVER_ERROR)
                .build();
    }


    private List<ClientDTO> toDTOs(List<Client> clients) {
        return clients.stream().map(this::toDTO).collect(Collectors.toList());
    }

    private ClientDTO toDTO(Client client) {
        return new ClientDTO(
            client.getId(),
            client.getEmail(),
            client.getName(),
            client.getBirthDate(),
            client.getContact(),
            administratorToDTO(client.getAdministrator())
        );
    }

    private AdministratorDTO administratorToDTO(Administrator administrator) {
        return new AdministratorDTO(
                administrator.getId(),
                administrator.getEmail(),
                administrator.getName());
    }
}
